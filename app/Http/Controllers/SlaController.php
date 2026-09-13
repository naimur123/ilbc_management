<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Request as WorkRequest;
use App\Models\RequestSla;
use App\Models\SlaConfiguration;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SlaMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SlaController extends Controller
{
    public function __construct(private SlaMonitoringService $sla)
    {
    }

    /**
     * SLA Pending / SLA Active / Due Soon / Overdue / Completed / SLA
     * Exceptions — refreshAll() runs on every visit so status is never
     * more than a page load stale even between cron ticks (see
     * ProcessSlaMonitoring / routes/console.php for the scheduled version).
     */
    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('sla.view'));

        $this->sla->refreshAll();

        $filter = $request->filter;

        $query = RequestSla::with('request.customer', 'responsibleUser', 'requestItem.product');

        $query = match ($filter) {
            'pending' => $query->where('status', 'PENDING'),
            'active' => $query->where('status', 'ACTIVE'),
            'due_soon' => $query->where('status', 'DUE_SOON'),
            'overdue' => $query->where('status', 'OVERDUE'),
            'completed' => $query->whereIn('status', ['COMPLETED_WITHIN_SLA', 'COMPLETED_LATE']),
            'exceptions' => $query->where('status', 'WAIVED'),
            default => $query->whereNotIn('status', RequestSla::FINAL_STATUSES),
        };

        $slas = $query->orderBy('target_at')->paginate(20)->withQueryString();

        // For the manual "Create SLA" panel (spec: "...or allow an
        // authorized user to create it") — only offered to sla.manage.
        $eligibleRequests = [];
        $users = [];
        if ($request->user()->can('sla.manage')) {
            $eligibleRequests = WorkRequest::with('customer')
                ->whereNotIn('status', ['DRAFT', 'CANCELLED', 'CLOSED'])
                ->latest()->limit(200)->get();
            $users = User::orderBy('name')->get();
        }

        return view('sla.index', compact('slas', 'filter', 'eligibleRequests', 'users'));
    }

    public function show(RequestSla $requestSla)
    {
        Gate::denyIf(! auth()->user()->can('sla.view'));

        $requestSla->load('request.customer', 'requestItem.product', 'responsibleUser', 'escalatedToUser', 'completedBy', 'waivedBy', 'statusHistory.changedBy', 'configuration');

        return view('sla.show', compact('requestSla'));
    }

    public function store(Request $httpRequest)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.manage'));

        $data = $httpRequest->validate([
            'request_id' => 'required|exists:requests,id',
            'sla_type' => 'required|in:ACTIVATION,DELIVERY,RESPONSE,RESOLUTION,CUSTOM',
            'priority' => 'required|in:LOW,NORMAL,HIGH,CRITICAL',
            'start_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'responsible_user_id' => 'nullable|exists:users,id',
            'responsible_team' => 'nullable|string|max:100',
            'reminder_before_minutes' => 'nullable|integer|min:0',
            'escalate_after_minutes' => 'nullable|integer|min:0',
        ]);

        $data['duration_minutes'] = (int) $data['duration_minutes'];
        $data['reminder_before_minutes'] = isset($data['reminder_before_minutes'])
            ? (int) $data['reminder_before_minutes']
            : null;
        $data['escalate_after_minutes'] = isset($data['escalate_after_minutes'])
            ? (int) $data['escalate_after_minutes']
            : null;

        $startAt = \Illuminate\Support\Carbon::parse($data['start_at']);

        $sla = RequestSla::create([
            'request_id' => $data['request_id'],
            'sla_type' => $data['sla_type'],
            'priority' => $data['priority'],
            'start_at' => $startAt,
            'target_at' => $startAt->copy()->addMinutes($data['duration_minutes']),
            'duration_minutes' => $data['duration_minutes'],
            'reminder_before_minutes' => $data['reminder_before_minutes'] ?? 60,
            'escalate_after_minutes' => $data['escalate_after_minutes'] ?? 120,
            'responsible_user_id' => $data['responsible_user_id'] ?? null,
            'responsible_team' => $data['responsible_team'] ?? null,
            'status' => $startAt->isFuture() ? 'PENDING' : 'ACTIVE',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('sla.show', $sla)->with('success', 'SLA created.');
    }

    public function complete(Request $httpRequest, RequestSla $requestSla)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.manage'));

        $data = $httpRequest->validate([
            'remarks' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($httpRequest->hasFile('attachment')) {
            $path = $httpRequest->file('attachment')->store('sla/'.$requestSla->id, 'public');
            $requestSla->update([
                'attachment_path' => $path,
                'attachment_original_name' => $httpRequest->file('attachment')->getClientOriginalName(),
            ]);
        }

        $this->sla->complete($requestSla, $data['remarks'] ?? null);

        return back()->with('success', 'SLA marked completed.');
    }

    public function waive(Request $httpRequest, RequestSla $requestSla)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.waive'));

        $data = $httpRequest->validate(['waived_reason' => 'required|string']);

        $this->sla->waive($requestSla, $data['waived_reason']);

        return back()->with('success', 'SLA waived.');
    }

    public function configuration(Request $request)
    {
        Gate::denyIf(! $request->user()->can('sla.configure'));

        $configurations = SlaConfiguration::with('product', 'productCategory', 'customer', 'department')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $requireBeforeAudit = $this->sla->completionRequiredBeforeAudit();

        return view('sla.configuration', compact('configurations', 'products', 'categories', 'customers', 'departments', 'requireBeforeAudit'));
    }

    public function storeConfiguration(Request $httpRequest)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.configure'));

        $data = $this->validatedConfiguration($httpRequest);
        SlaConfiguration::create($data);

        return back()->with('success', 'SLA configuration saved.');
    }

    public function updateConfiguration(Request $httpRequest, SlaConfiguration $slaConfiguration)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.configure'));

        $data = $this->validatedConfiguration($httpRequest);
        $slaConfiguration->update($data);

        return back()->with('success', 'SLA configuration updated.');
    }

    public function destroyConfiguration(Request $httpRequest, SlaConfiguration $slaConfiguration)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.configure'));

        $slaConfiguration->delete();

        return back()->with('success', 'SLA configuration removed.');
    }

    public function updateSettings(Request $httpRequest)
    {
        Gate::denyIf(! $httpRequest->user()->can('sla.configure'));

        $data = $httpRequest->validate(['completion_required_before_audit' => 'nullable|boolean']);

        SystemSetting::updateOrCreate(
            ['group' => 'sla', 'key' => 'completion_required_before_audit'],
            ['value' => $httpRequest->boolean('completion_required_before_audit') ? '1' : '0', 'cast_type' => 'boolean']
        );

        return back()->with('success', 'SLA settings saved.');
    }

    /**
     * SLA Performance Report: compliance %, breach count, and the
     * customer/product/employee/department breakdowns from the spec.
     */
    public function report(Request $request)
    {
        Gate::denyIf(! $request->user()->can('sla.report'));

        $slas = RequestSla::with('request.customer', 'requestItem.product', 'responsibleUser')
            ->whereIn('status', ['COMPLETED_WITHIN_SLA', 'COMPLETED_LATE'])
            ->get();

        $totalCompleted = $slas->count();
        $withinSla = $slas->where('status', 'COMPLETED_WITHIN_SLA')->count();
        $compliancePercent = $totalCompleted > 0 ? round($withinSla / $totalCompleted * 100, 1) : 0;

        $avgCompletionMinutes = $totalCompleted > 0
            ? round($slas->avg(fn ($s) => $s->start_at->diffInMinutes($s->completed_at)), 0)
            : 0;

        $byCustomer = $slas->groupBy(fn ($s) => $s->request->customer->name ?? 'Unknown')
            ->map(fn ($group) => [
                'total' => $group->count(),
                'within' => $group->where('status', 'COMPLETED_WITHIN_SLA')->count(),
            ]);

        $byProduct = $slas->groupBy(fn ($s) => $s->requestItem->product->name ?? 'All items')
            ->map(fn ($group) => [
                'total' => $group->count(),
                'within' => $group->where('status', 'COMPLETED_WITHIN_SLA')->count(),
            ]);

        $byResponsible = $slas->groupBy(fn ($s) => $s->responsibleUser->name ?? 'Unassigned')
            ->map(fn ($group) => [
                'total' => $group->count(),
                'within' => $group->where('status', 'COMPLETED_WITHIN_SLA')->count(),
            ]);

        $monthlyTrend = $slas->groupBy(fn ($s) => $s->completed_at->format('M-Y'))
            ->map(fn ($group) => [
                'total' => $group->count(),
                'within' => $group->where('status', 'COMPLETED_WITHIN_SLA')->count(),
            ]);

        $openCounts = RequestSla::whereNotIn('status', RequestSla::FINAL_STATUSES)
            ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('sla.report', compact(
            'totalCompleted', 'withinSla', 'compliancePercent', 'avgCompletionMinutes',
            'byCustomer', 'byProduct', 'byResponsible', 'monthlyTrend', 'openCounts'
        ));
    }

    private function validatedConfiguration(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'sla_type' => 'required|in:ACTIVATION,DELIVERY,RESPONSE,RESOLUTION,CUSTOM',
            'product_id' => 'nullable|exists:products,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'customer_id' => 'nullable|exists:customers,id',
            'department_id' => 'nullable|exists:departments,id',
            'priority' => 'nullable|in:LOW,NORMAL,HIGH,CRITICAL',
            'duration_minutes' => 'required|integer|min:1',
            'reminder_before_minutes' => 'nullable|integer|min:0',
            'escalate_after_minutes' => 'nullable|integer|min:0',
            'responsible_team' => 'nullable|string|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
