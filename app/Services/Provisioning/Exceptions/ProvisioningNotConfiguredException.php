<?php

namespace App\Services\Provisioning\Exceptions;

use RuntimeException;

/**
 * Thrown by a live provider (Partner Center, Crayon, ...) when it is asked to
 * act for a vendor that has no valid vendor_api_credentials row yet, or when
 * the global `provisioning.enabled` feature flag is off.
 *
 * This must never be swallowed into a silent no-op — callers should catch it
 * and surface a clear "vendor automation is not configured" message, and the
 * request should fall back to manual loading rather than appear to hang.
 */
class ProvisioningNotConfiguredException extends RuntimeException
{
    public static function forVendor(int $vendorId, string $provider): self
    {
        return new self(
            "Provisioning provider [{$provider}] is not configured for vendor #{$vendorId}. ".
            'Add valid credentials in Vendor Management > Vendors > API Credentials, or switch '.
            'this vendor back to the Manual provisioning provider.'
        );
    }

    public static function globallyDisabled(): self
    {
        return new self(
            'Vendor API automation is disabled system-wide. Enable it under System Settings > '.
            'Integration Settings before assigning a live provider to any vendor.'
        );
    }
}
