<?php

namespace App\Services\Provisioning;

use App\Contracts\VendorProvisioningServiceInterface;

/**
 * Default provider for every vendor until API automation is explicitly
 * turned on. Does not call any external API — it simply records whatever
 * the Loader already typed into the Loading screen (Subscription ID,
 * Tenant/Account, Activation Date, etc.) as the "external reference",
 * so the rest of the system (Audit variance detection, timeline, reports)
 * behaves identically whether a request was loaded manually or via a live
 * provider.
 *
 * This class is what keeps Sections 18-20 of the master prompt (fully
 * manual Loading) working unchanged while the provisioning abstraction
 * exists underneath it.
 */
class ManualProvisioningProvider implements VendorProvisioningServiceInterface
{
    public function create(ProvisioningRequest $request): ProvisioningResult
    {
        // The "external reference" for manual loading is whatever the Loader
        // typed as Subscription ID / Vendor Reference — passed in via metadata.
        $reference = $request->metadata['subscription_id']
            ?? $request->metadata['vendor_reference']
            ?? null;

        if (! $reference) {
            return ProvisioningResult::failed(
                'Manual loading requires a Subscription ID or Vendor Reference to be entered on the Loading screen.'
            );
        }

        return ProvisioningResult::succeeded($reference, [
            'provider' => 'manual',
            'recorded_at' => now()->toIso8601String(),
        ]);
    }

    public function suspend(string $externalReference): ProvisioningResult
    {
        // Manual suspension is a status change made by a human in the vendor's
        // own portal; we just acknowledge the reference we already have.
        return ProvisioningResult::succeeded($externalReference, ['provider' => 'manual', 'action' => 'suspend']);
    }

    public function resume(string $externalReference): ProvisioningResult
    {
        return ProvisioningResult::succeeded($externalReference, ['provider' => 'manual', 'action' => 'resume']);
    }

    public function delete(string $externalReference): ProvisioningResult
    {
        return ProvisioningResult::succeeded($externalReference, ['provider' => 'manual', 'action' => 'delete']);
    }

    public function getStatus(string $externalReference): ProvisioningResult
    {
        // Manual provider has no independent source of truth for status;
        // callers should rely on loading_records/audit_records instead.
        return ProvisioningResult::succeeded($externalReference, ['provider' => 'manual', 'status' => 'UNKNOWN']);
    }

    public function syncCatalog(): iterable
    {
        return [];
    }
}
