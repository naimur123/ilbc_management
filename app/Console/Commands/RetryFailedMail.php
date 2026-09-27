<?php

namespace App\Console\Commands;

use App\Models\FailedMailLog;
use App\Services\Email\FailedMailRetryService;
use Illuminate\Console\Command;

class RetryFailedMail extends Command
{
    protected $signature = 'mail:retry-failed';

    protected $description = 'Retry failed emails that are eligible for automatic delivery';

    public function handle(FailedMailRetryService $retryService): int
    {
        $now = now();
        $failedBefore = $now->copy()->subMinutes(5);
        $staleBefore = $now->copy()->subMinutes(FailedMailRetryService::STALE_PROCESSING_MINUTES);
        $attempted = 0;
        $sent = 0;

        FailedMailLog::query()
            ->where('attempt_count', '<', FailedMailRetryService::AUTO_MAX_ATTEMPTS)
            ->where(function ($query) use ($failedBefore, $staleBefore) {
                $query->where(function ($query) use ($failedBefore) {
                    $query->where('status', 'failed')
                        ->where('failed_at', '<=', $failedBefore);
                })->orWhere(function ($query) use ($staleBefore) {
                    $query->where('status', 'processing')
                        ->where('last_attempt_at', '<=', $staleBefore);
                });
            })
            ->orderBy('id')
            ->chunkById(100, function ($logs) use ($retryService, &$attempted, &$sent) {
                foreach ($logs as $log) {
                    $attempted++;

                    if ($retryService->retry($log, automatic: true)) {
                        $sent++;
                    }
                }
            });

        $this->info("Failed email retries: {$sent} of {$attempted} sent.");

        return self::SUCCESS;
    }
}