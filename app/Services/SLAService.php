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

        $deadline = Carbon::parse($request->current_stage_started_at)->addHours($rule->sla_hours);
        $dueSoonFrom = $deadline->copy()->subHours($rule->due_soon_threshold_hours);
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
