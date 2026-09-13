<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SLA Management (change request, Sept 2026): recalculates every open
// request_slas row's status and sends due/overdue/escalation reminders.
// Requires the cPanel cron entry from README section A6
// (`php artisan schedule:run` every minute) to actually fire.
Schedule::command('sla:process')->everyFifteenMinutes();
