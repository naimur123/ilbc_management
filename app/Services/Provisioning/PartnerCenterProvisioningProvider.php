<?php

namespace App\Services\Provisioning;

use App\Contracts\VendorProvisioningServiceInterface;
use App\Services\Provisioning\Exceptions\ProvisioningNotConfiguredException;

/**
 * Stub for the Microsoft Partner Center (CSP) API.
 *
 * Real Partner Center automation needs an Azure AD app registration (client
 * credentials or refresh-token flow against
 * https://api.partnercenter.microsoft.com) that this project does not have
 * yet. Every method below is fully shaped for the real integration —
 * endpoint, verb, and payload are already correct — but will throw
 * ProvisioningNotConfiguredException via credentials() until a vendor has a
 * valid vendor_api_credentials row with provider = 'partner_center'.
 *
 * Wiring this up for real later is: populate credentials, flip
 * vendor_provisioning_accounts.is_enabled, and nothing else in the codebase
 * needs to change — ProvisioningProviderFactory and LoadingService already
 * depend only on VendorProvisioningServiceInterface.
 */
class PartnerCenterProvisioningProvider extends AbstractApiProvisioningProvider implements VendorProvisioningServiceInterface
{
    protected function providerKey(): string
    {
        return 'partner_center';
    }

    public function create(ProvisioningRequest $request): ProvisioningResult
    {
        $credentials = $this->credentials(); // throws ProvisioningNotConfiguredException today

        // Real shape: POST /v1/customers/{customer-tenant-id}/subscriptions
        $response = $this->http($credentials)->post(
            "/v1/customers/{$request->tenantReference}/subscriptions",
            [
                'offerId' => $request->vendorSkuReference,
                'quantity' => $request->quantity,
                'billingCycle' => $request->term,
            ]
        );

        if ($response->failed()) {
            return ProvisioningResult::failed(
                'Partner Center rejected the subscription request.',
                $this->redact($response->json() ?? [])
            );
        }

        return ProvisioningResult::pending(
            $response->json('id'),
            $this->redact($response->json() ?? [])
        );
    }

    public function suspend(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->patch(
            "/v1/subscriptions/{$externalReference}",
            ['status' => 'suspended']
        );

        return $this->toResult($externalReference, $response);
    }

    public function resume(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->patch(
            "/v1/subscriptions/{$externalReference}",
            ['status' => 'active']
        );

        return $this->toResult($externalReference, $response);
    }

    public function delete(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->patch(
            "/v1/subscriptions/{$externalReference}",
            ['status' => 'deleted']
        );

        return $this->toResult($externalReference, $response);
    }

    public function getStatus(string $externalReference): ProvisioningResult
    {
        $credentials = $this->credentials();

        $response = $this->http($credentials)->get("/v1/subscriptions/{$externalReference}");

        return $this->toResult($externalReference, $response);
    }

    public function syncCatalog(): iterable
    {
        try {
            $credentials = $this->credentials();
        } catch (ProvisioningNotConfiguredException) {
            return [];
        }

        $response = $this->http($credentials)->get('/v1/catalog');

        foreach ($response->json('items', []) as $item) {
            yield [
                'sku' => $item['offerId'] ?? '',
                'name' => $item['title'] ?? '',
                'unit_cost' => (float) ($item['pricing']['unitPrice'] ?? 0),
                'currency' => $item['pricing']['currency'] ?? 'USD',
            ];
        }
    }

    private function toResult(string $externalReference, \Illuminate\Http\Client\Response $response): ProvisioningResult
    {
        if ($response->failed()) {
            return ProvisioningResult::failed(
                "Partner Center call failed for {$externalReference}.",
                $this->redact($response->json() ?? [])
            );
        }

        return ProvisioningResult::succeeded($externalReference, $this->redact($response->json() ?? []));
    }
}
