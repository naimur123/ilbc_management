<?php

namespace App\Services;

use App\Models\Request as WorkRequest;
use App\Models\SlaRule;
use Carbon\Carbon;

/**
 * Section 34: stage-wise SLA status (On Time / Due Soon / Overdue) driven
 * entirely by the configurable sla_rules table — never hard-coded hours.
 */
class SLAService
{
    public function statusFor(WorkRequest $request): string
    {
        if (in_array($request->current_stage, ['CLOSURE'], true) && $request->status === 'CLOSED') {
            return 'COMPLETED';
        }

        $rule = SlaRule::where('stage_key', $request->current_stage)->first();

        if (! $rule || ! $request->current_stage_started_at) {
            return 'ON_TIME';
        }

        // Defensive: sla_hours/due_soon_threshold_hours are NOT NULL in the
        // schema, but a blank "SLA (hours)" field saved from Workflow &
        // Settings (resources/views/admin/workflow-settings.blade.php) can
        // still land here as null/0 on some MySQL configurations — without
        // this guard, Carbon::addHours(null) throws a fatal TypeError and
        // takes down every page that shows SLA status (Dashboard, Requests).
        $slaHours = (int) ($rule->sla_hours ?: 24);
        $dueSoonThresholdHours = (int) ($rule->due_soon_threshold_hours ?: 1);

        $deadline = Carbon::parse($request->current_stage_started_at)->addHours($slaHours);
        $dueSoonFrom = $deadline->copy()->subHours($dueSoonThresholdHours);
        $now = now();

        if ($now->greaterThan($deadline)) {
            return 'OVERDUE';
        }

        if ($now->greaterThanOrEqualTo($dueSoonFrom)) {
            return 'DUE_SOON';
        }

        return 'ON_TIME';
    }

    public function hoursPending(WorkRequest $request): float
    {
        if (! $request->current_stage_started_at) {
            return 0.0;
        }

        return round(Carbon::parse($request->current_stage_started_at)->diffInMinutes(now()) / 60, 1);
    }
}
