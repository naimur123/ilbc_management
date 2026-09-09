<?php

namespace App\Services;

use App\Exceptions\LoadingProvisioningException;
use App\Models\LoadingRecord;
use App\Models\RequestItem;
use App\Models\Vendor;
use App\Services\Provisioning\Exceptions\ProvisioningNotConfiguredException;
use App\Services\Provisioning\ProvisioningProviderFactory;
use App\Services\Provisioning\ProvisioningRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Section 18-20. markCompleted() always goes through
 * ProvisioningProviderFactory — today every vendor defaults to the
 * ManualProvisioningProvider, so this simply confirms the Loader's typed
 * fields; a vendor switched to a live provider later needs no change here.
 */
class LoadingService
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function saveDraft(RequestItem $item, array $data): LoadingRecord
    {
        $record = $item->loadingRecord ?? new LoadingRecord(['request_item_id' => $item->id]);
        $record->fill($data);
        $record->status = 'IN_PROGRESS';
        $record->loaded_by = Auth::id();
        $record->save();

        return $record;
    }

    /**
     * @throws LoadingProvisioningException when the vendor's live provider
     *         (Partner Center/Crayon) is enabled but not yet configured —
     *         surfaced to the Loader rather than silently falling back, per
     *         the provisioning design's "never a silent no-op" rule.
     */
    public function markCompleted(RequestItem $item, array $data): LoadingRecord
    {
        $record = $this->saveDraft($item, $data);
        $vendor = $item->vendorSelection?->vendor;

        if ($vendor instanceof Vendor) {
            try {
                $provider = ProvisioningProviderFactory::for($vendor);
                $provider->create(new ProvisioningRequest(
                    requestItemId: $item->id,
                    loadingRecordId: $record->id,
                    action: 'CREATE',
                    externalReference: null,
                    vendorSkuReference: $item->sku->sku_code,
                    tenantReference: $record->tenant_account,
                    quantity: (int) $record->actual_loaded_quantity,
                    term: $item->sku->term,
                    metadata: [
                        'subscription_id' => $record->subscription_id,
                        'vendor_reference' => $record->vendor_reference,
                    ],
                ));
            } catch (ProvisioningNotConfiguredException $e) {
                throw new LoadingProvisioningException($e->getMessage(), previous: $e);
            }
        }

        $record->update(['status' => 'COMPLETED', 'completed_at' => now()]);

        $this->auditLog->record('Loading', 'Loading marked completed', $item->request_id, null, $record->subscription_id);

        return $record;
    }
}
