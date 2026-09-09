<?php

namespace App\Services\Provisioning;

use App\Contracts\VendorProvisioningServiceInterface;
use App\Services\Provisioning\Exceptions\ProvisioningNotConfiguredException;

/**
 * Stub for Crayon's CloudIQ API.
 *
 * Same pattern as PartnerCenterProvisioningProvider: fully shaped requests
 * against Crayon's CloudIQ endpoints, but every call throws
 * ProvisioningNotConfiguredException via credentials() until a vendor row
 * exists in vendor_api_credentials with provider = 'crayon'. No sandbox
 * access exists yet, so the exact resource paths below should be confirmed
 * against Crayon's current CloudIQ API reference before this stub is
 * switched to live use.
 */
class CrayonProvisioningProvider extends AbstractApiProvisioningProvider implements VendorProvisioningServiceInterface
{
    protected function providerKey(): string
    {
        return 'crayon';
    }

    public function create(ProvisioningRequest $request): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->post('/api/v1/organizations/'.$request->tenantReference.'/subscriptions', [
            'productId' => $request->vendorSkuReference,
            'quantity' => $request->quantity,
            'term' => $request->term,
        ]);

        if ($response->failed()) {
            return ProvisioningResult::failed(
                'Crayon rejected the subscription request.',
                $this->redact($response->json() ?? [])
            );
        }

        return ProvisioningResult::pending(
            $response->json('subscriptionId'),
            $this->redact($response->json() ?? [])
        );
    }

    public function suspend(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->post("/api/v1/subscriptions/{$externalReference}/suspend");

        return $this->toResult($externalReference, $response);
    }

    public function resume(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->post("/api/v1/subscriptions/{$externalReference}/resume");

        return $this->toResult($externalReference, $response);
    }

    public function delete(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->delete("/api/v1/subscriptions/{$externalReference}");

        return $this->toResult($externalReference, $response);
    }

    public function getStatus(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->get("/api/v1/subscriptions/{$externalReference}");

        return $this->toResult($externalReference, $response);
    }

    public function syncCatalog(): iterable
    {
        try {
            $credentials = $this->credentials();
        } catch (ProvisioningNotConfiguredException) {
            return [];
        }

        $response = $this->http($credentials)->get('/api/v1/products');

        foreach ($response->json('items', []) as $item) {
            yield [
                'sku' => $item['productId'] ?? '',
                'name' => $item['displayName'] ?? '',
                'unit_cost' => (float) ($item['unitCost'] ?? 0),
                'currency' => $item['currency'] ?? 'USD',
            ];
        }
    }

    private function toResult(string $externalReference, \Illuminate\Http\Client\Response $response): ProvisioningResult
    {
        if ($response->failed()) {
            return ProvisioningResult::failed(
                "Crayon call failed for {$externalReference}.",
                $this->redact($response->json() ?? [])
            );
        }

        return ProvisioningResult::succeeded($externalReference, $this->redact($response->json() ?? []));
    }
}
