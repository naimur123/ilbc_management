<?php

namespace App\Http\Controllers;

use App\Models\BillingType;
use App\Models\Customer;
use App\Models\Department;
use App\Models\PaymentTerm;
use App\Models\ProductCategory;
use App\Models\Request as WorkRequest;
use App\Models\RequestAttachment;
use App\Models\RequestItem;
use App\Models\Salesperson;
use App\Models\SubscriptionType;
use App\Services\AuditLogService;
use App\Services\ClosureService;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RequestController extends Controller
{
    public function __construct(
        private WorkflowService $workflow,
        private NotificationService $notifications,
        private AuditLogService $auditLog,
    ) {
    }

    public function index(HttpRequest $request)
    {
        $user = Auth::user();

        $requests = WorkRequest::with(['customer', 'salesperson'])
            ->when($request->boolean('mine') || ! $user->can('request.view_all'), function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('created_by', $user->id)
                        ->orWhereHas('salesperson', fn ($q3) => $q3->where('user_id', $user->id));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, function ($q) use ($request) {
                $q->where('request_no', 'like', "%{$request->q}%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$request->q}%"));
            })
            ->latest()->paginate(20)->withQueryString();

        return view('requests.index', compact('requests'));
    }

    public function create()
    {
        Gate::denyIf(! Auth::user()->can('request.create'));

        return view('requests.create', [
            'customers' => Customer::where('status', 'ACTIVE')->orderBy('name')->get(),
            'salespersons' => Salesperson::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'paymentTerms' => PaymentTerm::orderBy('name')->get(),
            'categories' => ProductCategory::with(['products.skus'])->orderBy('sort_order')->get(),
            'billingTypes' => BillingType::orderBy('name')->get(),
            'subscriptionTypes' => SubscriptionType::orderBy('name')->get(),
        ]);
    }

    public function store(HttpRequest $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'salesperson_id' => 'required|exists:salespersons,id',
            'department_id' => 'nullable|exists:departments,id',
            'work_order_no' => 'nullable|string|max:60',
            'po_number' => 'nullable|string|max:60',
            'order_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:150',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'source_lead' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:50',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'advance_percent' => 'nullable|integer|min:0|max:100',
            'credit_days' => 'nullable|integer|min:0|max:365',
            'billing_cycle' => 'nullable|string|max:30',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_category_id' => 'required|exists:product_categories,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_sku_id' => 'required|exists:product_skus,id',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.billing_type_id' => 'nullable|exists:billing_types,id',
            'items.*.subscription_type_id' => 'nullable|exists:subscription_types,id',
            'items.*.start_date' => 'nullable|date',
            'items.*.end_date' => 'nullable|date',
            'items.*.unit_selling_price' => 'required|numeric|min:0',
            'order_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'quotation_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'submit_action' => 'required|in:draft,submit',
        ]);

        $workRequest = DB::transaction(function () use ($data, $request) {
            $workRequest = WorkRequest::create([
                'request_no' => WorkRequest::generateRequestNo(),
                'customer_id' => $data['customer_id'],
                'salesperson_id' => $data['salesperson_id'],
                'department_id' => $data['department_id'] ?? null,
                'work_order_no' => $data['work_order_no'] ?? null,
                'po_number' => $data['po_number'] ?? null,
                'order_date' => $data['order_date'] ?? null,
                'status' => $data['submit_action'] === 'submit' ? 'BILLING_CLEARANCE_PENDING' : 'DRAFT',
                'current_stage' => $data['submit_action'] === 'submit' ? 'BILLING_CLEARANCE' : 'SALES',
                'created_by' => Auth::id(),
                'submitted_at' => $data['submit_action'] === 'submit' ? now() : null,
                'current_stage_started_at' => now(),
            ]);

            $workRequest->salesEntry()->create([
                'contact_person' => $data['contact_person'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'source_lead' => $data['source_lead'] ?? null,
                'customer_type' => $data['customer_type'] ?? null,
                'payment_terms_id' => $data['payment_terms_id'] ?? null,
                'advance_percent' => $data['advance_percent'] ?? 0,
                'credit_days' => $data['credit_days'] ?? 0,
                'billing_cycle' => $data['billing_cycle'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                RequestItem::create([
                    'request_id' => $workRequest->id,
                    'product_category_id' => $itemData['product_category_id'],
                    'product_id' => $itemData['product_id'],
                    'product_sku_id' => $itemData['product_sku_id'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'billing_type_id' => $itemData['billing_type_id'] ?? null,
                    'subscription_type_id' => $itemData['subscription_type_id'] ?? null,
                    'start_date' => $itemData['start_date'] ?? null,
                    'end_date' => $itemData['end_date'] ?? null,
                    'unit_selling_price' => $itemData['unit_selling_price'],
                    'total_selling_price' => $itemData['quantity'] * $itemData['unit_selling_price'],
                ]);
            }

            foreach (['order_upload' => 'ORDER', 'quotation_upload' => 'QUOTATION'] as $field => $type) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('requests/'.$workRequest->id, 'public');
                    RequestAttachment::create([
                        'request_id' => $workRequest->id,
                        'type' => $type,
                        'original_name' => $request->file($field)->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $request->file($field)->getMimeType(),
                        'size_bytes' => $request->file($field)->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            $this->workflow->ensureInstance($workRequest);

            if ($data['submit_action'] === 'submit') {
                $this->workflow->transition($workRequest, 'BILLING_CLEARANCE_PENDING', 'BILLING_CLEARANCE', 'SUBMIT', 'Submitted by Sales');
                $this->notifications->notifyPermission('billing.clearance.approve', 'Billing clearance needed', "Request {$workRequest->request_no} needs billing clearance.", $workRequest->id, route('billing-clearance.show', $workRequest));
            }

            $this->auditLog->record('Sales', 'Request created', $workRequest->id);

            return $workRequest;
        });

        return redirect()->route('requests.show', $workRequest)->with('success', 'Request '.$workRequest->request_no.' created.');
    }

    public function show(WorkRequest $request)
    {
        Gate::authorize('view', $request);

        $request->load([
            'customer', 'salesperson', 'salesEntry', 'attachments',
            'items.category', 'items.product', 'items.sku', 'items.billingType', 'items.subscriptionType',
            'items.vendorSelection.vendor', 'items.loadingRecord', 'items.auditRecord',
            'billingClearances', 'reviewerApproval.checklists', 'billingRecord', 'invoices', 'closure',
            'comments.user', 'reopenRequests',
        ]);

        $timeline = $this->workflow->timeline($request);

        return view('requests.show', compact('request', 'timeline'));
    }

    public function edit(WorkRequest $request)
    {
        Gate::authorize('update', $request);

        return view('requests.edit', ['workRequest' => $request->load('items', 'salesEntry')]);
    }

    public function update(HttpRequest $httpRequest, WorkRequest $request)
    {
        Gate::authorize('update', $request);

        $data = $httpRequest->validate([
            'work_order_no' => 'nullable|string|max:60',
            'po_number' => 'nullable|string|max:60',
            'remarks' => 'nullable|string',
        ]);

        $request->update($data);
        $request->salesEntry?->update(['remarks' => $data['remarks'] ?? null]);
        $this->auditLog->record('Sales', 'Request header updated', $request->id);

        return back()->with('success', 'Request updated.');
    }

    public function destroy(WorkRequest $request)
    {
        Gate::authorize('delete', $request);
        $request->delete();

        return redirect()->route('requests.index')->with('success', 'Draft deleted.');
    }

    public function submit(WorkRequest $request)
    {
        if ($request->status !== 'DRAFT') {
            return back()->with('error', 'Only draft requests can be submitted.');
        }

        $this->workflow->transition($request, 'BILLING_CLEARANCE_PENDING', 'BILLING_CLEARANCE', 'SUBMIT', 'Submitted by Sales');
        $this->notifications->notifyPermission('billing.clearance.approve', 'Billing clearance needed', "Request {$request->request_no} needs billing clearance.", $request->id, route('billing-clearance.show', $request));

        return back()->with('success', 'Request submitted for Billing Clearance.');
    }

    public function cancel(HttpRequest $httpRequest, WorkRequest $request)
    {
        Gate::authorize('cancel', $request);
        $reason = $httpRequest->validate(['reason' => 'required|string'])['reason'];

        $request->update(['status' => 'CANCELLED']);
        $this->auditLog->record('Sales', 'Request cancelled', $request->id, null, $reason);

        return back()->with('success', 'Request cancelled.');
    }

    public function requestReopen(HttpRequest $httpRequest, WorkRequest $request, ClosureService $closureService)
    {
        $data = $httpRequest->validate([
            'target_stage' => 'required|in:'.implode(',', WorkRequest::STAGES),
            'reason' => 'required|string',
        ]);

        $closureService->requestReopen($request, $data['target_stage'], $data['reason']);

        return back()->with('success', 'Reopen request submitted for approval.');
    }

    public function approveReopen(\App\Models\ReopenRequest $reopen, ClosureService $closureService)
    {
        Gate::denyIf(! Auth::user()->can('closure.reopen'));
        $closureService->approveReopen($reopen);

        return back()->with('success', 'Request reopened to '.$reopen->target_stage.'.');
    }

    public function addComment(HttpRequest $httpRequest, WorkRequest $request)
    {
        $data = $httpRequest->validate(['body' => 'required|string']);
        $request->comments()->create(['user_id' => Auth::id(), 'body' => $data['body']]);

        return back()->with('success', 'Comment added.');
    }
}
