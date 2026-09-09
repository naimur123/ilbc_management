<?php

namespace App\Services;

use App\Models\Closure;
use App\Models\Request as WorkRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Section 26/27: Rule 5 — closure only after Loading Completed AND Audit
 * Approved AND Invoice Generated AND Sent AND Billing Done. canClose()
 * is the single gate every controller/Blade view must check before
 * offering the Close button.
 *
 * Change request (Sept 2026), Section 10: "Closure cannot be completed
 * simply by clicking a Close button. The Closure Team must complete a
 * final checklist" — Billing Verification + Collection Verification, both
 * mandatory, on top of the automatic status checks above.
 */
class ClosureService
{
    /**
     * Section 10's Closure Checklist. Billing Verification mirrors the
     * invoice fields already captured in Billing & Invoice; Collection
     * Verification is new — recorded via saveCollection() onto
     * billing_records (migration 2026_01_01_000161).
     */
    public const CHECKLIST_ITEMS = [
        'invoice_generated' => 'Invoice generated / recorded',
        'invoice_number_entered' => 'Invoice number entered',
        'invoice_date_entered' => 'Invoice date entered',
        'invoice_amount_verified' => 'Invoice amount verified',
        'vat_tax_verified' => 'VAT / TAX information verified where applicable',
        'billing_document_attached' => 'Billing supporting document attached',
        'collection_status_checked' => 'Collection status checked',
        'collection_amount_entered' => 'Collection amount entered',
        'collection_date_entered' => 'Collection date entered',
        'payment_reference_entered' => 'Payment reference entered',
        'payment_method_recorded' => 'Bank / payment method recorded',
        'outstanding_amount_calculated' => 'Outstanding amount calculated',
    ];

    public function __construct(private AuditLogService $auditLog, private NotificationService $notifications)
    {
    }

    /**
     * Seeds the request's closure checklist the first time the Closure
     * Team opens it — same auto-seed pattern already used for the
     * Reviewer/Loading/Audit checklists.
     */
    public function ensureChecklist(WorkRequest $request): void
    {
        if ($request->closureChecklists()->count() > 0) {
            return;
        }

        foreach (self::CHECKLIST_ITEMS as $key => $label) {
            $request->closureChecklists()->create(['check_key' => $key, 'label' => $label, 'is_mandatory' => true]);
        }
    }

    public function allChecklistChecked(WorkRequest $request): bool
    {
        return $request->closureChecklists()->where('is_mandatory', true)->where('is_checked', false)->doesntExist();
    }

    public function canClose(WorkRequest $request): bool
    {
        $itemsLoaded = $request->items->every(fn ($item) => $item->loadingRecord?->status === 'COMPLETED');
        $itemsAudited = $request->items->every(fn ($item) => $item->auditRecord?->decision === 'APPROVE');
        $billing = $request->billingRecord;

        $billingDone = $itemsLoaded && $itemsAudited
            && $billing?->invoice_generated_at && $billing?->invoice_sent_at && $billing?->billing_done_at;

        return $billingDone && $this->allChecklistChecked($request);
    }

    public function close(WorkRequest $request, ?string $remarks = null): Closure
    {
        $closure = Closure::create([
            'request_id' => $request->id,
            'closed_by' => Auth::id(),
            'closure_date' => now()->toDateString(),
            'closure_time' => now()->toTimeString(),
            'remarks' => $remarks,
        ]);

        $request->update(['status' => 'CLOSED', 'current_stage' => 'CLOSURE']);

        $this->auditLog->record('Closure', 'Request closed', $request->id);

        return $closure;
    }

    public function requestReopen(WorkRequest $request, string $targetStage, string $reason): void
    {
        $request->reopenRequests()->create([
            'reason' => $reason,
            'target_stage' => $targetStage,
            'requested_by' => Auth::id(),
            'requested_at' => now(),
            'status' => 'PENDING',
        ]);

        $this->notifications->notifyPermission('closure.reopen', 'Reopen requested', "Request {$request->request_no} has a pending reopen request.", $request->id);
    }

    public function approveReopen(\App\Models\ReopenRequest $reopen, ?string $remarks = null): void
    {
        $reopen->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'decision_remarks' => $remarks,
        ]);

        $reopen->request->update([
            'status' => 'REOPENED',
            'current_stage' => $reopen->target_stage,
            'current_stage_started_at' => now(),
        ]);

        $this->auditLog->record('Closure', "Reopened to {$reopen->target_stage}", $reopen->request_id, null, $reopen->reason);
    }
}
