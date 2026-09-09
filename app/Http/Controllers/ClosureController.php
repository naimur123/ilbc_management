<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkRequest;
use App\Services\ClosureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClosureController extends Controller
{
    public function __construct(private ClosureService $closureService)
    {
    }

    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('closure.view'));

        $requests = WorkRequest::with('customer', 'billingRecord', 'closure', 'items.vendorSelection', 'items.auditRecord')
            ->when($request->filter === 'closed', fn ($q) => $q->where('status', 'CLOSED'))
            ->when($request->filter === 'reopened', fn ($q) => $q->where('status', 'REOPENED'))
            ->when(!$request->filter, fn ($q) => $q->where('status', 'BILLING_DONE'))
            ->latest()->paginate(20);

        return view('closure.index', compact('requests'));
    }

    public function show(WorkRequest $request)
    {
        Gate::denyIf(! auth()->user()->can('closure.view'));
        $request->load('customer', 'items.loadingRecord', 'items.auditRecord', 'items.vendorSelection', 'billingRecord', 'invoices', 'closure', 'reopenRequests', 'closureChecklists');

        $this->closureService->ensureChecklist($request);
        $request->load('closureChecklists');

        $canClose = $this->closureService->canClose($request);

        return view('closure.show', compact('request', 'canClose'));
    }

    /**
     * Section 10's "Collection Verification" — the Closure Team records
     * collection status/amount/date/reference/method here (there is no
     * other screen in the app that captures this), onto billing_records.
     */
    public function saveCollection(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('closure.close') && ! $httpRequest->user()->can('closure.view'));

        $data = $httpRequest->validate([
            'collection_status' => 'nullable|in:PENDING,PARTIAL,RECEIVED',
            'collection_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:60',
            'outstanding_amount' => 'nullable|numeric|min:0',
        ]);

        $billing = $request->billingRecord ?? $request->billingRecord()->create([]);
        $billing->fill($data + ['collection_recorded_by' => Auth::id(), 'collection_recorded_at' => now()]);
        $billing->save();

        return back()->with('success', 'Collection details saved.');
    }

    public function saveChecklist(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('closure.close') && ! $httpRequest->user()->can('closure.view'));

        $this->closureService->ensureChecklist($request);
        $checked = $httpRequest->input('checked', []);

        foreach ($request->closureChecklists as $item) {
            $item->update(['is_checked' => in_array((string) $item->id, $checked, true)]);
        }

        return back()->with('success', 'Closure checklist saved.');
    }

    public function close(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('closure.close'));

        if (! $this->closureService->canClose($request)) {
            return back()->with('error', 'Cannot close: Loading, Audit, Invoice Generated/Sent, Billing Done and the Closure Checklist must all be complete first.');
        }

        $data = $httpRequest->validate(['remarks' => 'nullable|string']);
        $this->closureService->close($request, $data['remarks'] ?? null);

        return redirect()->route('closure.index')->with('success', 'Request closed.');
    }
}
