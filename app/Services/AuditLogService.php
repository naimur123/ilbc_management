<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Section 30: every critical action writes one row here. Never exposed
 * through any update/delete route — this is an append-only trail.
 */
class AuditLogService
{
    public function record(string $module, string $action, ?int $requestId = null, mixed $oldValue = null, mixed $newValue = null): AuditLog
    {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'role_name' => $user?->getRoleNames()->first(),
            'request_id' => $requestId,
            'module' => $module,
            'action' => $action,
            'old_value' => $this->stringify($oldValue),
            'new_value' => $this->stringify($newValue),
            'ip_address' => RequestFacade::ip(),
            'user_agent' => substr((string) RequestFacade::userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }

    private function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : json_encode($value);
    }
}
