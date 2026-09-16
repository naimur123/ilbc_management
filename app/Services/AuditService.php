<?php

namespace App\Services;

use App\Models\AuditRecord;
use App\Models\AuditVariance;
use App\Models\RequestItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Section 21-24. detectVariances() is the automatic check from Section 23 —
 * it always runs (not just the manual checklist) and compares three
 * sources: the Reviewer-approved snapshot (vendor_selections + the item
 * itself as approved) against what the Loader actually entered
 * (loading_records). Any mismatch is flagged so the Audit screen can
 * highlight it in red, per the spec.
 */
class AuditService
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function ensureRecord(RequestItem $item): AuditRecord
    {
        return AuditRecord::firstOrCreate(
            ['request_item_id' => $item->id],
            ['loading_record_id' => $item->loadingRecord->id]
        );
    }

    /**
     * @return array<int, array{field_key:string, label:string, approved:mixed, actual:mixed, mismatch:bool}>
     */
    public function detectVariances(RequestItem $item): array
    {
        $loading = $item->loadingRecord;
        $selection = $item->vendorSelection;

        $checks = [
            ['key' => 'product', 'label' => 'Product', 'approved' => $item->product->name, 'actual' => $item->product->name],
            ['key' => 'sku', 'label' => 'SKU', 'approved' => $item->sku->sku_code, 'actual' => $item->sku->sku_code],
            ['key' => 'quantity', 'label' => 'Quantity', 'approved' => (float) $item->quantity, 'actual' => (float) ($loading->actual_loaded_quantity ?? 0)],
            ['key' => 'vendor', 'label' => 'Vendor', 'approved' => $selection?->vendor?->name, 'actual' => $selection?->vendor?->name],
            // Tenant / Account is no longer approved at Reviewer stage (change
            // request, Sept 2026) — it is assigned order-wise, per item, only
            // during Loading, so there is no "approved" value left to diff it
            // against automatically. The Loader-entered value still shows on
            // this screen's Loading Information tab, and "Correct Tenant"
            // stays in the Auditor's manual checklist below.
            ['key' => 'start_date', 'label' => 'Start / Activation Date', 'approved' => optional($item->start_date)->toDateString(), 'actual' => optional($loading->activation_date)->toDateString()],
            ['key' => 'end_date', 'label' => 'End / Expiry Date', 'approved' => optional($item->end_date)->toDateString(), 'actual' => optional($loading->expiry_date)->toDateString()],
        ];

        return array_map(function (array $check) {
            $mismatch = (string) $check['approved'] !== (string) $check['actual'];

            return [
                'field_key' => $check['key'],
                'label' => $check['label'],
                'approved' => $check['approved'],
                'actual' => $check['actual'],
                'mismatch' => $mismatch,
            ];
        }, $checks);
    }

    public function persistVariances(AuditRecord $record, array $variances): void
    {
        DB::transaction(function () use ($record, $variances) {
            $record->variances()->delete();

            foreach ($variances as $variance) {
                AuditVariance::create([
                    'audit_record_id' => $record->id,
                    'field_key' => $variance['field_key'],
                    'label' => $variance['label'],
                    'approved_value' => (string) $variance['approved'],
                    'actual_value' => (string) $variance['actual'],
                    'is_mismatch' => $variance['mismatch'],
                ]);
            }

            $record->update(['has_variance' => collect($variances)->contains('mismatch', true)]);
        });
    }

    public function approve(AuditRecord $record, ?string $remarks = null): void
    {
        $record->update([
            'decision' => 'APPROVE',
            'remarks' => $remarks,
            'audited_by' => Auth::id(),
            'audited_at' => now(),
        ]);

        $this->auditLog->record('Audit', 'Audit approved, sent to Billing', $record->requestItem->request_id);
    }

    /**
     * @param array|string $categories one or more category strings
     */
    public function returnToLoader(AuditRecord $record, array|string $categories, string $remarks): void
    {
        $record->update([
            'decision' => 'RETURN',
            'remarks' => $remarks,
            'audited_by' => Auth::id(),
            'audited_at' => now(),
        ]);

        $cats = is_array($categories) ? $categories : [$categories];
        foreach ($cats as $cat) {
            $record->corrections()->create(['category' => $cat, 'remarks' => $remarks]);
        }
        $record->loadingRecord->update(['status' => 'IN_PROGRESS']);

        $catList = implode(', ', $cats);
        $this->auditLog->record('Audit', "Returned to Loader for correction: {$catList}", $record->requestItem->request_id, null, $remarks);
    }

    public function hold(AuditRecord $record, string $remarks): void
    {
        $record->update([
            'decision' => 'HOLD',
            'remarks' => $remarks,
            'audited_by' => Auth::id(),
            'audited_at' => now(),
        ]);

        $this->auditLog->record('Audit', 'Audit put on hold', $record->requestItem->request_id, null, $remarks);
    }
}
