<?php

namespace App\Console\Commands;

use App\Services\SlaMonitoringService;
use Illuminate\Console\Command;

/**
 * Runs on cPanel's cron via `php artisan schedule:run` (see routes/console.php
 * and README section A6) so SLA reminders, Due Soon/Overdue transitions and
 * escalations fire even when nobody has the app open.
 */
class ProcessSlaMonitoring extends Command
{
    protected $signature = 'sla:process';

    protected $description = 'Recalculate every open SLA\'s status and send due/overdue/escalation reminders';

    public function handle(SlaMonitoringService $sla): int
    {
        $stats = $sla->refreshAll();

        $this->info("SLA processed: {$stats['transitioned']} status change(s), {$stats['reminders']} reminder(s), {$stats['escalations']} escalation(s).");

        return self::SUCCESS;
    }
}
