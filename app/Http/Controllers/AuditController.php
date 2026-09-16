<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\NotificationService;
use App\Services\SlaMonitoringService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    /**
     * Section 22's manual checklist — automatic variance detection
     * (Section 23) runs separately via AuditService::detectVariances()
     * and is never a substitute for these items.
     */
    public const CHECKLIST_ITEMS = [
        'correct_customer' => 'Correct Customer',
        'correct_tenant' => 'Correct Tenant',
        'product_match' => 'Product Match',
        'sku_match' => 'SKU Match',
        'quantity_match' => 'Quantity Match',
        'vendor_match' => 'Vendor Match',
        'loading_source_match' => 'Loading Source Match',
        'subscription_type_match' => 'Subscription Type Match',
        'activation_date_verified' => 'Activation Date Verified',
        'expiry_date_verified' => 'Expiry Date Verified',
        'subscription_id_verified' => 'Subscription ID Verified',
        'vendor_reference_verified' => 'Vendor Reference Verified',
        'selling_price_verified' => 'Selling Price Verified',
        'vendor_cost_verified' => 'Vendor Cost Verified',
        'final_landed_cost_verified' => 'Final Landed Cost Verified',
        'profit_calculation_verified' => 'Profit Calculation Verified',
        'margin_verified' => 'Margin % Verified',
        'reviewer_instruction_followed' => 'Reviewer Instruction Followed',
        'proof_attached' => 'Screenshot / Proof Attached',
        'work_order_po_match' => 'Work Order / PO Match',
        'duplicate_loading_check' => 'Duplicate Loading Check',
        'technical_notes_complete' => 'Technical Notes Complete',
        'exception_check' => 'Exception Check',
        'documents_complete' => 'Documents Complete',
        'ready_for_billing' => 'Ready for Billing',
    ];

    public function __construct(
        private AuditService $auditService,
        private WorkflowService $workflow,
        private NotificationService $notifications,
        private SlaMonitoringService $slaMonitoring,
    ) {
    }

    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('audit.view'));

        $items = RequestItem::with('request.customer', 'product', 'sku', 'vendorSelection.vendor', 'loadingRecord.loadedBy')
            ->whereHas('request', fn ($q) => $q->where('status', 'AUDIT_PENDING'))
            ->when($request->filter === 'approved', fn ($q) => $q->whereHas('auditRecord', fn ($q2) => $q2->where('decision', 'APPROVE')))
            ->when($request->filter === 'returned', fn ($q) => $q->whereHas('auditRecord', fn ($q2) => $q2->where('decision', 'RETURN')))
            ->when($request->filter === 'hold', fn ($q) => $q->whereHas('auditRecord', fn ($q2) => $q2->where('decision', 'HOLD')))
            ->latest()->paginate(20);

        return view('audit.index', compact('items'));
    }

    public function show(RequestItem $item)
    {
        Gate::denyIf(! auth()->user()->can('audit.view'));

        $item->load('request.customer', 'request.salesperson', 'request.reviewerApproval.loadingSourceVendor', 'product', 'sku', 'billingType', 'vendorSelection.vendor', 'loadingRecord.attachments', 'loadingRecord.commitmentType', 'loadingRecord.billingType');

        $record = $this->auditService->ensureRecord($item);

        if ($record->checklists()->count() === 0) {
            foreach (self::CHECKLIST_ITEMS as $key => $label) {
                $record->checklists()->create(['check_key' => $key, 'label' => $label]);
            }
        }

        $variances = $this->auditService->detectVariances($item);
        $this->auditService->persistVariances($record, $variances);
        $record->refresh()->load('checklists');

        // SLA Management (change request, Sept 2026) — "SLA Before Audit":
        // the Auditor sees Target vs Actual / Met-Breached for every SLA
        // commitment on this request, and, if the Admin has switched on
        // "SLA Completion Required Before Audit", Approve is blocked here.
        $this->slaMonitoring->refreshAll();
        $item->request->load('slas');
        $slaSummary = $this->slaMonitoring->summaryForRequest($item->request);
        $slaBlocksAudit = $this->slaMonitoring->auditIsBlocked($item->request);
        return view('audit.show', compact('item', 'record', 'variances', 'slaSummary', 'slaBlocksAudit'));
    }

    public function decide(Request $httpRequest, RequestItem $item, BillingService $billingService)
    {
        Gate::denyIf(! $httpRequest->user()->can('audit.approve') && ! $httpRequest->user()->can('audit.return') && ! $httpRequest->user()->can('audit.hold'));

        $data = $httpRequest->validate([
            'decision' => 'required|in:APPROVE,RETURN,HOLD',
            'remarks' => 'nullable|string',
            'correction_category' => 'nullable|array',
            'correction_category.*' => 'nullable|string|max:40',
            'checked' => 'nullable|array',
        ]);

        if ($data['decision'] !== 'APPROVE' && empty($data['remarks'])) {
            return back()->withErrors(['remarks' => 'Reason is mandatory for Return/Hold.']);
        }

        // SLA Management: only enforced when the Admin has switched on
        // "SLA Completion Required Before Audit" in SLA Configuration —
        // otherwise SLA is a performance measurement only and never blocks.
        if ($data['decision'] === 'APPROVE' && $this->slaMonitoring->auditIsBlocked($item->request)) {
            return back()->withErrors(['remarks' => 'This request cannot be Audit-approved yet — its SLA is still open and "SLA Completion Required Before Audit" is enabled. Complete or waive the SLA first.']);
        }

        $record = $item->auditRecord;
        $checked = $data['checked'] ?? [];
        foreach ($record->checklists as $c) {
            $c->update(['is_checked' => in_array((string) $c->id, $checked, true)]);
        }

        if ($data['decision'] === 'APPROVE') {
            $this->auditService->approve($record, $data['remarks'] ?? null);

            $request = $item->request;

            if ($request->items->every(fn (RequestItem $i) => $i->fresh()->auditRecord?->decision === 'APPROVE')) {
                $this->workflow->transition($request, 'BILLING_PENDING', 'BILLING', 'AUDIT_APPROVE', $data['remarks'] ?? null);
                $this->notifications->notifyPermission('invoice.create', 'Billing required', "Request {$request->request_no} is ready for Billing/Invoice.", $request->id, route('billing-invoice.index'));
            }
        } elseif ($data['decision'] === 'RETURN') {
            $this->auditService->returnToLoader($record, $data['correction_category'] ?? ['Other'], $data['remarks']);
            $item->request->update(['status' => 'AUDIT_RETURNED', 'current_stage' => 'LOADING']);
        } else {
            $this->auditService->hold($record, $data['remarks']);
        }

        return redirect()->route('audit.index')->with('success', 'Decision recorded.');
    }
}
