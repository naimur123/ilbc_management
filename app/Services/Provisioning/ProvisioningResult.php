<?php

namespace App\Services\Provisioning;

/**
 * Uniform result returned by every VendorProvisioningServiceInterface method,
 * regardless of provider. Controllers/Services only ever deal with this shape,
 * never with a raw Partner Center or Crayon response.
 */
class ProvisioningResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $status,           // e.g. PENDING|RUNNING|SUCCEEDED|FAILED
        public readonly ?string $externalReference = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],          // redacted provider payload, for provisioning_logs
    ) {
    }

    public static function failed(string $message, array $raw = []): self
    {
        return new self(success: false, status: 'FAILED', message: $message, raw: $raw);
    }

    public static function pending(string $externalReference, array $raw = []): self
    {
        return new self(success: true, status: 'PENDING', externalReference: $externalReference, raw: $raw);
    }

    public static function succeeded(string $externalReference, array $raw = []): self
    {
        return new self(success: true, status: 'SUCCEEDED', externalReference: $externalReference, raw: $raw);
    }
}
