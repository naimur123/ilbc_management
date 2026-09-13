<?php

namespace App\Http\Controllers;

use App\Exceptions\LoadingProvisioningException;
use App\Models\LoadingAttachment;
use App\Models\LoadingRecord;
use App\Models\Request as WorkRequest;
use App\Models\RequestItem;
use App\Services\AuditLogService;
use App\Services\LoadingService;
use App\Services\NotificationService;
use App\Services\SlaMonitoringService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LoadingController extends Controller
{
    /**
     * Section 20's Loader checklist — auto-seeded onto the loading record
     * the first time the Loader opens it, same pattern as Reviewer's.
     */
    public const CHECKLIST_ITEMS = [
        'received_instruction' => 'Received Approved Instruction',
        'product_verified' => 'Product Verified',
        'sku_verified' => 'SKU Verified',
        'quantity_verified' => 'Quantity Verified',
        'vendor_verified' => 'Correct Vendor Selected',
        'tenant_verified' => 'Correct Tenant Used',
        'subscription_created' => 'Subscription Created',
        'license_assigned' => 'License Assigned',
        'activation_verified' => 'Activation Verified',
        'screenshot_attached' => 'Screenshot Attached',
    ];

    public function __construct(
        private LoadingService $loadingService,
        private WorkflowService $workflow,
        private NotificationService $notifications,
        private AuditLogService $auditLog,
        private SlaMonitoringService $slaMonitoring,
    ) {
    }

    /**
     * Redesigned Loading queue (change request, Sept 2026): one row per
     * REQUEST — the customer's whole order — not per item, matching the
     * "Main List" mockup (Request No. / Customer / Product-SKU / Qty /
     * Vendor / Approval Date / Status / Action = Review). Loading itself
     * still happens per item underneath (Section 42's multi-vendor support
     * is unaffected); "Review" opens the customer-wise/order-wise
     * assignment screen listing every item in the request.
     */
    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('loading.view'));

        $filter = $request->filter;

        $query = WorkRequest::with(
            'customer', 'reviewerApproval.loadingSourceVendor',
            'items.product', 'items.sku', 'items.vendorSelection.vendor', 'items.loadingRecord.loadedBy',
        );

        if ($filter === 'completed') {
            // Judged from the items' own loadingRecord state, independent of
            // the request's current status, so a request that has already
            // moved on to Audit/Closure still shows up in this history tab.
            $query->whereHas('items')->whereDoesntHave('items', fn ($q) => $q->whereDoesntHave('loadingRecord')
                ->orWhereHas('loadingRecord', fn ($q2) => $q2->where('status', '!=', 'COMPLETED')));
        } elseif ($filter === 'mine') {
            $query->whereIn('status', ['LOADING_PENDING', 'LOADING_IN_PROGRESS', 'AUDIT_RETURNED'])
                ->whereHas('items.loadingRecord', fn ($q) => $q->where('loaded_by', Auth::id()));
        } else {
            $query->whereIn('status', ['LOADING_PENDING', 'LOADING_IN_PROGRESS', 'AUDIT_RETURNED']);
        }

        $requests = $query->latest()->paginate(20);

        return view('loading.index', compact('requests', 'filter'));
    }

    /**
     * Section 3's "Review" action: customer-wise + order-wise vendor & SKU
     * assignment for every item in the request, before Loading detail entry.
     * The commercial vendor/cost per item stays frozen from Reviewer
     * approval (Section 41) — this screen assigns/confirms the Tenant /
     * Account each item will be loaded under, order line by order line.
     */
    public function review(WorkRequest $request)
    {
        Gate::denyIf(! auth()->user()->can('loading.view'));

        $request->load(
            'customer', 'salesperson', 'reviewerApproval.loadingSourceVendor',
            'items.category', 'items.product', 'items.sku', 'items.vendorSelection.vendor',
            'items.loadingRecord.checklists', 'items.loadingRecord.attachments',
        );

        $timeline = $this->workflow->timeline($request);

        return view('loading.review', compact('request', 'timeline'));
    }

    public function saveAssignments(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('loading.process'));

        $data = $httpRequest->validate([
            'tenant_account' => 'nullable|array',
            'tenant_account.*' => 'nullable|string|max:150',
        ]);

        foreach ($request->items as $item) {
            if (! array_key_exists($item->id, $data['tenant_account'] ?? [])) {
                continue;
            }

            $record = $item->loadingRecord ?? new LoadingRecord(['request_item_id' => $item->id]);
            $record->tenant_account = $data['tenant_account'][$item->id];

            if (! $record->exists) {
                $record->status = 'IN_PROGRESS';
                $record->loaded_by = Auth::id();
            }

            $record->save();
        }

        $request->update(['status' => 'LOADING_IN_PROGRESS']);
        $this->auditLog->record('Loading', 'Customer-wise / order-wise vendor & SKU assignment saved', $request->id);

        return redirect()->route('loading.review', $request)->with('success', 'Assignment saved for all items.');
    }

    public function show(RequestItem $item)
    {
        Gate::denyIf(! auth()->user()->can('loading.view'));

        $item->load('request.customer', 'request.reviewerApproval', 'product', 'sku', 'vendorSelection.vendor', 'loadingRecord.checklists', 'loadingRecord.attachments');

        if ($item->loadingRecord && $item->loadingRecord->checklists->count() === 0) {
            foreach (self::CHECKLIST_ITEMS as $key => $label) {
                $item->loadingRecord->checklists()->create(['check_key' => $key, 'label' => $label]);
            }
            $item->refresh();
        }

        return view('loading.show', compact('item'));
    }

    public function saveDraft(Request $httpRequest, RequestItem $item)
    {
        Gate::denyIf(! $httpRequest->user()->can('loading.process'));
        $data = $this->validated($httpRequest);

        $record = $this->loadingService->saveDraft($item, $data);
        $this->syncChecklist($httpRequest, $record);
        $this->handleUploads($httpRequest, $record);

        $item->request()->update(['status' => 'LOADING_IN_PROGRESS']);

        return back()->with('success', 'Loading draft saved.');
    }

    public function complete(Request $httpRequest, RequestItem $item)
    {
        Gate::denyIf(! $httpRequest->user()->can('loading.complete'));
        $httpRequest->validate(['loading_date' => 'required|date', 'actual_loaded_quantity' => 'required|numeric|min:0.01']);
        $data = $this->validated($httpRequest);

        try {
            $record = $this->loadingService->markCompleted($item, $data);
        } catch (LoadingProvisioningException $e) {
            return back()->with('error', 'Could not complete loading: '.$e->getMessage());
        }

        $this->syncChecklist($httpRequest, $record);
        $this->handleUploads($httpRequest, $record);

        $request = $item->request;

        if ($request->items->every(fn (RequestItem $i) => $i->fresh()->loadingRecord?->status === 'COMPLETED')) {
            $this->workflow->transition($request, 'AUDIT_PENDING', 'AUDIT', 'LOADING_COMPLETE', 'All items loaded');
            // SLA Management (change request, Sept 2026): the SLA clock
            // starts the moment every item in the request finishes Loading.
            $this->slaMonitoring->createForRequest($request);
            $this->notifications->notifyPermission('audit.approve', 'Audit required', "Request {$request->request_no} is ready for Audit.", $request->id, route('audit.index'));
        }

        $this->auditLog->record('Loading', 'Item loading completed', $request->id);

        return redirect()->route('loading.index')->with('success', 'Loading marked completed.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'loading_date' => 'nullable|date',
            'loading_time' => 'nullable',
            'actual_loaded_quantity' => 'nullable|numeric|min:0',
            'subscription_id' => 'nullable|string|max:150',
            'license_id' => 'nullable|string|max:150',
            'tenant_account' => 'nullable|string|max:150',
            'activation_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'vendor_reference' => 'nullable|string|max:150',
            'distributor_reference' => 'nullable|string|max:150',
            'po_reference' => 'nullable|string|max:150',
            'technical_notes' => 'nullable|string',
            'screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'vendor_confirmation' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'csp_screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'subscription_confirmation' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Files are validated above but never mass-assigned onto the loading
        // record itself — handleUploads() stores them as separate attachments.
        return \Illuminate\Support\Arr::except($validated, ['screenshot', 'vendor_confirmation', 'csp_screenshot', 'subscription_confirmation']);
    }

    private function syncChecklist(Request $request, $record): void
    {
        $checked = $request->input('checked', []);

        foreach ($record->checklists as $item) {
            $item->update(['is_checked' => in_array((string) $item->id, $checked, true)]);
        }
    }

    private function handleUploads(Request $request, $record): void
    {
        foreach (['screenshot' => 'SCREENSHOT', 'vendor_confirmation' => 'VENDOR_CONFIRMATION', 'csp_screenshot' => 'CSP_SCREENSHOT', 'subscription_confirmation' => 'SUBSCRIPTION_CONFIRMATION'] as $field => $type) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('loading/'.$record->id, 'public');
                LoadingAttachment::create([
                    'loading_record_id' => $record->id,
                    'type' => $type,
                    'original_name' => $request->file($field)->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $request->file($field)->getMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }
    }
}
