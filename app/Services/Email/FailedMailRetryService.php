<?php

namespace App\Services\Email;

use App\Models\FailedMailLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FailedMailRetryService
{
    public const AUTO_MAX_ATTEMPTS = 5;

    public const STALE_PROCESSING_MINUTES = 15;

    public function __construct(private TemplateMailService $mailService)
    {
    }

    public function retry(FailedMailLog $failure, bool $automatic = false): bool
    {
        $retryLog = DB::transaction(function () use ($failure, $automatic) {
            $log = FailedMailLog::query()->lockForUpdate()->find($failure->getKey());

            if (! $log) {
                return null;
            }

            $staleProcessing = $log->status === 'processing'
                && $log->last_attempt_at?->lte(now()->subMinutes(self::STALE_PROCESSING_MINUTES));

            if ($log->status !== 'failed' && ! $staleProcessing) {
                return null;
            }

            if ($automatic && $log->attempt_count >= self::AUTO_MAX_ATTEMPTS) {
                return null;
            }

            $log->update([
                'status' => 'processing',
                'attempt_count' => $log->attempt_count + 1,
                'last_attempt_at' => now(),
            ]);

            return $log;
        });

        if (! $retryLog) {
            return false;
        }

        try {
            return $this->mailService->send(
                $retryLog->template_slug,
                $retryLog->payload ?? [],
                $retryLog->to_email ?? '',
                $retryLog->attachments ?? [],
                $retryLog->transport ?: 'smtp',
                $retryLog,
            );
        } catch (Throwable $exception) {
            $retryLog->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'error_trace' => $exception->getTraceAsString(),
                'failed_at' => now(),
            ]);

            Log::channel('custom_daily')->error('Failed email retry threw an exception', [
                'failed_mail_log_id' => $retryLog->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}