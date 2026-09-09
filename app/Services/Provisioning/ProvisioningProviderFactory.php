<?php

namespace App\Services\Provisioning;

use App\Contracts\VendorProvisioningServiceInterface;
use App\Models\Vendor;
use InvalidArgumentException;

/**
 * Resolves the correct VendorProvisioningServiceInterface implementation for
 * a vendor, based on vendor_provisioning_accounts.provider.
 *
 * This is the ONLY place in the codebase that should instantiate a concrete
 * provider — LoadingService, controllers, and queued jobs all depend on the
 * interface and ask this factory for an instance, so adding a new provider
 * later (e.g. a fourth distributor) never touches calling code.
 */
class ProvisioningProviderFactory
{
    public static function for(Vendor $vendor): VendorProvisioningServiceInterface
    {
        $provider = $vendor->provisioningAccount?->provider ?? 'manual';

        return match ($provider) {
            'manual' => new ManualProvisioningProvider(),
            'partner_center' => new PartnerCenterProvisioningProvider($vendor),
            'crayon' => new CrayonProvisioningProvider($vendor),
            default => throw new InvalidArgumentException("Unknown provisioning provider [{$provider}] for vendor #{$vendor->id}."),
        };
    }
}
