<?php

namespace App\Services\Provisioning;

use App\Models\Vendor;
use App\Models\VendorApiCredential;
use App\Models\VendorProvisioningAccount;
use App\Services\Provisioning\Exceptions\ProvisioningNotConfiguredException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Shared plumbing for real (non-manual) providers: credential lookup,
 * the global feature-flag check, and a redacting HTTP client helper so
 * provisioning_logs never end up with secrets in them.
 *
 * Concrete providers (Partner Center, Crayon) only need to implement the
 * provider name and the actual endpoint calls in terms of $this->http().
 */
abstract class AbstractApiProvisioningProvider
{
    public function __construct(protected Vendor $vendor)
    {
    }

    abstract protected function providerKey(): string; // 'partner_center' | 'crayon'

    /**
     * Resolves this vendor's provisioning account + decrypts its credentials,
     * or throws ProvisioningNotConfiguredException — every public method on a
     * concrete provider should call this first.
     */
    protected function credentials(): array
    {
        if (! config('provisioning.enabled')) {
            throw ProvisioningNotConfiguredException::globallyDisabled();
        }

        /** @var VendorProvisioningAccount|null $account */
        $account = VendorProvisioningAccount::query()
            ->where('vendor_id', $this->vendor->id)
            ->where('provider', $this->providerKey())
            ->where('is_enabled', true)
            ->first();

        /** @var VendorApiCredential|null $credential */
        $credential = $account?->credentials()->latest()->first();

        if (! $account || ! $credential || $credential->isExpired()) {
            throw ProvisioningNotConfiguredException::forVendor($this->vendor->id, $this->providerKey());
        }

        return json_decode(Crypt::decryptString($credential->encrypted_payload), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Pending<Illuminate\Http\Client\PendingRequest> pre-authenticated for
     * this provider, with a redacting middleware so nothing sensitive ever
     * reaches provisioning_logs.
     */
    protected function http(array $credentials): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($credentials['base_url'] ?? '')
            ->withToken($credentials['access_token'] ?? null)
            ->timeout(30)
            ->retry(2, 500);
    }

    /**
     * Strips anything that looks like a secret before it is written to
     * provisioning_logs.request_payload / response_payload.
     */
    protected function redact(array $payload): array
    {
        $pattern = '/token|secret|password|authorization|api[_-]?key/i';

        array_walk_recursive($payload, function (&$value, $key) use ($pattern) {
            if (is_string($key) && preg_match($pattern, $key)) {
                $value = '***REDACTED***';
            }
        });

        return $payload;
    }
}
