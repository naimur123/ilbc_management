<?php

namespace App\Contracts;

use App\Services\Provisioning\ProvisioningRequest;
use App\Services\Provisioning\ProvisioningResult;

/**
 * Provider-agnostic contract for automating vendor-side license/subscription
 * lifecycle actions (Microsoft Partner Center, Crayon CloudIQ, or any future
 * distributor API). LoadingService and the Loading controller depend only on
 * this interface — never on a concrete provider — so the active provider can
 * be swapped per vendor via config/database without touching business logic.
 *
 * Every implementation MUST:
 *  - be safe to call when unconfigured (throw ProvisioningNotConfiguredException,
 *    never silently pretend to succeed)
 *  - never log or persist raw credentials
 *  - return a ProvisioningResult so callers never branch on provider-specific
 *    response shapes
 */
interface VendorProvisioningServiceInterface
{
    /**
     * Create a new subscription/license for the customer described in $request.
     */
    public function create(ProvisioningRequest $request): ProvisioningResult;

    /**
     * Suspend an existing subscription/license (does not delete it).
     */
    public function suspend(string $externalReference): ProvisioningResult;

    /**
     * Resume a previously suspended subscription/license.
     */
    public function resume(string $externalReference): ProvisioningResult;

    /**
     * Cancel/delete a subscription/license.
     */
    public function delete(string $externalReference): ProvisioningResult;

    /**
     * Poll the current status of a subscription/license (used by the
     * webhook-less fallback path, and to reconcile after a webhook is missed).
     */
    public function getStatus(string $externalReference): ProvisioningResult;

    /**
     * Optional: pull the vendor's current live catalog/pricing for
     * reconciliation against vendor_product_prices. Providers that don't
     * support this may return an empty iterable.
     *
     * @return iterable<array{sku: string, name: string, unit_cost: float, currency: string}>
     */
    public function syncCatalog(): iterable;
}
