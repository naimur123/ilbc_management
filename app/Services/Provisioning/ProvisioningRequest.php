<?php

namespace App\Services\Provisioning;

/**
 * Immutable value object describing what should be provisioned with a vendor.
 *
 * Built entirely from data the workflow already collects (the approved
 * request item and the loading record) — provisioning never introduces a
 * new place to type commercial or product data by hand.
 */
class ProvisioningRequest
{
    public function __construct(
        public readonly int $requestItemId,
        public readonly ?int $loadingRecordId,
        public readonly string $action, // ProvisioningAction::CREATE|SUSPEND|RESUME|DELETE
        public readonly ?string $externalReference, // required for SUSPEND/RESUME/DELETE
        public readonly ?string $vendorSkuReference, // vendor's own SKU/offer id, if known
        public readonly ?string $tenantReference,    // e.g. customer's *.onmicrosoft.com domain or tenant GUID
        public readonly int $quantity,
        public readonly ?string $term = null,        // MONTHLY|ANNUAL|TRIENNIAL|NA
        public readonly array $metadata = [],
    ) {
    }
}
