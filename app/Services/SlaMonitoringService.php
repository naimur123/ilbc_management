<?php

namespace App\Services;

use App\Models\Request as WorkRequest;
use App\Models\RequestItem;
use App\Models\RequestSla;
use App\Models\SlaConfiguration;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Change request (Sept 2026) — the real SLA monitoring & reminder system
 * that sits right after Loading / Installation. This is deliberately built
 * as an overlay on top of the existing 8-stage workflow (requests.status /
 * WorkflowStep) rather than a rewrite of it: inserting "SLA" as a literal
 * new workflow step would mean touching every one of the ~15 places that
 * already switch on status strings like LOADING_PENDING/AUDIT_PENDING, for
 * a system already live on a real customer's server — high risk for no
 * functional gain, since the practical effect the user asked for (an SLA
 * clock that starts the moment Loading finishes and is watched until
 * Audit) is achieved just as well this way. request_slas is intentionally
 * separate from `requests` because one request can carry more than one
 * SLA commitment over time (Activation, then later Billing Submission).
 */
class SlaMonitoringService
{
    public function __construct(
        private NotificationService $notifications,
        private AuditLogService $auditLog,
    ) {
    }

    /**
     * Picks the most specific active configuration that matches this
     * request/item/priority, or null if the admin hasn't configured one —
     * callers fall back to a sane hard-coded default (see createForRequest).
     */
    public function resolveConfiguration(WorkRequest $request, ?RequestItem $item, string $slaType, string $priority = 'NORMAL'): ?SlaConfiguration
    {
        $productId = $item?->product_id;
        $categoryId = $item?->product_category_id;

        return SlaConfiguration::where('is_active', true)
            ->where('sla_type', $slaType)
            ->where(function ($q) use ($productId) {
                $q->whereNull('product_id')->orWhere('product_id', $productId);
            })
            ->where(function ($q) use ($categoryId) {
                $q->whereNull('product_category_id')->orWhere('product_category_id', $categoryId);
            })
            ->where(function ($q) use ($request) {
                $q->whereNull('customer_id')->orWhere('customer_id', $request->customer_id);
            })
            ->where(function ($q) use ($request) {
                $q->whereNull('department_id')->orWhere('department_id', $request->department_id);
            })
            ->where(function ($q) use ($priority) {
                $q->whereNull('priority')->orWhere('priority', $priority);
            })
            ->get()
            ->sortByDesc(fn (SlaConfiguration $c) => $c->specificity())
            ->first();
    }

    /**
     * Section 18/34's trigger: "After Loading / Installation is marked
     * completed, the system should create an SLA record automatically."
     * Called once, from LoadingController::complete(), the moment every
     * item in the request is COMPLETED. Idempotent — safe to call more
     * than once for the same request (e.g. if Loading is reopened later).
     */
    public function createForRequest(WorkRequest $request, string $slaType = 'ACTIVATION', string $priority = 'NORMAL'): ?RequestSla
    {
        if ($request->slas()->where('sla_type', $slaType)->exists()) {
            return null;
        }

        $config = $this->resolveConfiguration($request, $request->items->first(), $slaType, $priority);

        $startAt = now();
        // No configuration set up yet for this combination — fall back to
        // the 8-hour example from the spec so the module still works out
        // of the box before an Admin has configured anything.
        $durationMinutes = (int) ($config->duration_minutes ?? (8 * 60));
        $targetAt = $startAt->copy()->addMinutes($durationMinutes);

        $sla = RequestSla::create([
            'request_id' => $request->id,
            'sla_configuration_id' => $config?->id,
            'sla_type' => $slaType,
            'priority' => $config->priority ?? $priority,
            'start_at' => $startAt,
            'target_at' => $targetAt,
            'duration_minutes' => $durationMinutes,
            'reminder_before_minutes' => $config->reminder_before_minutes ?? 60,
            'escalate_after_minutes' => $config->escalate_after_minutes ?? 120,
            'responsible_team' => $config->responsible_team ?? null,
            'status' => 'ACTIVE',
            'created_by' => Auth::id(),
        ]);

        $this->recordHistory($sla, null, 'ACTIVE', null, 'SLA started automatically when Loading was completed.');
        $this->auditLog->record('SLA', 'SLA started', $request->id, null, "{$slaType} due ".$targetAt->format('d-m-Y H:i'));

        $this->notifications->notifyPermission('sla.manage', 'SLA started', "An SLA clock started for {$request->request_no} — due ".$targetAt->format('d-m-Y H:i').'.', $request->id, route('sla.show', $sla));

        return $sla;
    }

    /**
     * Pure calculation given "now" — never persists. refreshAll() below
     * decides what to do when the computed value differs from what's
     * stored (fire a reminder, escalate, write history).
     */
    public function computeStatus(RequestSla $sla, ?Carbon $now = null): string
    {
        if ($sla->status === 'WAIVED') {
            return 'WAIVED';
        }

        if ($sla->completed_at) {
            return $sla->completed_at->lte($sla->target_at) ? 'COMPLETED_WITHIN_SLA' : 'COMPLETED_LATE';
        }

        $now ??= now();

        if ($sla->start_at && $now->lt($sla->start_at)) {
            return 'PENDING';
        }

        $dueSoonFrom = $sla->target_at->copy()->subMinutes($sla->reminder_before_minutes ?? 60);

        if ($now->greaterThan($sla->target_at)) {
            return 'OVERDUE';
        }

        if ($now->greaterThanOrEqualTo($dueSoonFrom)) {
            return 'DUE_SOON';
        }

        return 'ACTIVE';
    }

    /**
     * Recomputes every open SLA's status, records transitions, and fires
     * the reminder/escalation notifications described in the spec. Called
     * by `php artisan sla:process` (scheduled every 15 minutes — see
     * routes/console.php) and defensively on every SLA index/dashboard
     * page load, so status is never more than a page-refresh stale even
     * if a host's cron hasn't fired yet.
     *
     * @return array{transitioned:int, reminders:int, escalations:int}
     */
    public function refreshAll(): array
    {
        $now = now();
        $stats = ['transitioned' => 0, 'reminders' => 0, 'escalations' => 0];

        RequestSla::whereNotIn('status', RequestSla::FINAL_STATUSES)
            ->with('request', 'responsibleUser')
            ->chunkById(100, function ($slas) use ($now, &$stats) {
                foreach ($slas as $sla) {
                    $this->refreshOne($sla, $now, $stats);
                }
            });

        return $stats;
    }

    private function refreshOne(RequestSla $sla, Carbon $now, array &$stats): void
    {
        $newStatus = $this->computeStatus($sla, $now);

        if ($newStatus !== $sla->status) {
            $old = $sla->status;
            $sla->status = $newStatus;
            $sla->save();
            $this->recordHistory($sla, $old, $newStatus, null, 'Automatically recalculated.');
            $stats['transitioned']++;

            if ($newStatus === 'OVERDUE') {
                $this->notifyResponsible($sla, 'SLA overdue', "Request {$sla->request->request_no}'s {$sla->sla_type} SLA is now overdue.");
            } elseif ($newStatus === 'DUE_SOON') {
                $this->notifyResponsible($sla, 'SLA due soon', "Request {$sla->request->request_no}'s {$sla->sla_type} SLA is due at ".$sla->target_at->format('d-m-Y H:i').'.');
            }
        }

        // One reminder before the deadline, guarded so it only ever fires once.
        if (! $sla->reminder_sent_at && $newStatus === 'DUE_SOON') {
            $this->notifyResponsible($sla, 'SLA reminder', "Reminder: request {$sla->request->request_no}'s SLA is due at ".$sla->target_at->format('d-m-Y H:i').'.');
            $sla->update(['reminder_sent_at' => $now]);
            $stats['reminders']++;
        }

        // Escalate once, `escalate_after_minutes` past the deadline.
        $escalateAfterMinutes = $sla->escalate_after_minutes;
        if (! $sla->escalated_at && $newStatus === 'OVERDUE' && $escalateAfterMinutes !== null
            && $now->greaterThanOrEqualTo($sla->target_at->copy()->addMinutes((int) $escalateAfterMinutes))) {
            $sla->update(['escalated_at' => $now]);
            $this->notifications->notifyPermission('sla.configure', 'SLA escalation', "Request {$sla->request->request_no}'s SLA is overdue by more than {$sla->escalate_after_minutes} minutes and needs management attention.", $sla->request_id, route('sla.show', $sla));
            $this->recordHistory($sla, $sla->status, $sla->status, null, 'Escalated to management — overdue beyond the configured escalation window.');
            $stats['escalations']++;
        }
    }

    private function notifyResponsible(RequestSla $sla, string $title, string $body): void
    {
        if ($sla->responsibleUser) {
            $this->notifications->notifyUser($sla->responsibleUser, $title, $body, $sla->request_id, route('sla.show', $sla));
        } else {
            $this->notifications->notifyPermission('sla.manage', $title, $body, $sla->request_id, route('sla.show', $sla));
        }
    }

    public function complete(RequestSla $sla, ?string $remarks, ?User $user = null): RequestSla
    {
        return DB::transaction(function () use ($sla, $remarks, $user) {
            $now = now();
            $old = $sla->status;

            $sla->update([
                'completed_at' => $now,
                'completed_by' => $user?->id ?? Auth::id(),
                'remarks' => $remarks,
                'status' => $now->lte($sla->target_at) ? 'COMPLETED_WITHIN_SLA' : 'COMPLETED_LATE',
            ]);

            $this->recordHistory($sla, $old, $sla->status, $user?->id ?? Auth::id(), $remarks);
            $this->auditLog->record('SLA', 'SLA completed ('.$sla->status.')', $sla->request_id, null, $remarks);

            return $sla->fresh();
        });
    }

    public function waive(RequestSla $sla, string $reason, ?User $approver = null): RequestSla
    {
        $old = $sla->status;

        $sla->update([
            'status' => 'WAIVED',
            'waived_reason' => $reason,
            'waived_by' => $approver?->id ?? Auth::id(),
            'waived_at' => now(),
        ]);

        $this->recordHistory($sla, $old, 'WAIVED', $approver?->id ?? Auth::id(), $reason);
        $this->auditLog->record('SLA', 'SLA waived', $sla->request_id, null, $reason);

        return $sla->fresh();
    }

    private function recordHistory(RequestSla $sla, ?string $from, string $to, ?int $changedBy, ?string $remarks): void
    {
        $sla->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $changedBy,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Section "SLA Before Audit" — a compact summary the Audit screen
     * shows next to Loading Information: target vs actual, met or breached.
     */
    public function summaryForRequest(WorkRequest $request): array
    {
        return $request->slas->map(fn (RequestSla $sla) => [
            'sla' => $sla,
            'met' => $sla->completed_at ? $sla->completed_at->lte($sla->target_at) : null,
            'actual_duration' => $sla->completed_at ? $sla->start_at->diff($sla->completed_at)->format('%hh %im') : null,
            'variance' => $sla->completed_at ? $sla->target_at->diffForHumans($sla->completed_at, true) : null,
        ])->all();
    }

    public function completionRequiredBeforeAudit(): bool
    {
        $setting = SystemSetting::where('group', 'sla')->where('key', 'completion_required_before_audit')->first();

        return $setting ? $setting->castValue() : false;
    }

    public function auditIsBlocked(WorkRequest $request): bool
    {
        if (! $this->completionRequiredBeforeAudit()) {
            return false;
        }

        return $request->slas->isNotEmpty() && $request->slas->contains(fn (RequestSla $s) => ! $s->isFinal());
    }
}
