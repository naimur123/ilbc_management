<?php

namespace App\Services\Email;

use App\Mail\DynamicTemplateMail;
use App\Models\EmailTemplates;
use App\Models\FailedMailLog;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Throwable;

class TemplateMailService
{
    public function __construct(protected EmailTemplateRender $renderer, protected MicrosoftGraphMailService $microsoftGraphMail)
    {
    }

    public function send(
        string $slug,
        array $data,
        string|array $to,
        array $attachments = [],
        string $transport = 'smtp',
        ?FailedMailLog $retryLog = null,
    ): bool
    {
        $template = EmailTemplates::with('email_attachments')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            throw new InvalidArgumentException("Email template not found or inactive: {$slug}");
        }

        $rendered = $this->renderer->renderTemplate($template->toArray(), $data);

        // Build attachments from template
        $templateAttachments = $template->email_attachments->map(function ($att) {
            return [
                'path' => $att->path,
                'name' => $att->name,
                'mime' => $att->mime,
            ];
        })->toArray();

        // Merge with any external attachments
        $finalAttachments = $templateAttachments;
        if (!empty($attachments)) {
            $finalAttachments = array_merge($templateAttachments, $attachments);
        }

        $mailable = new DynamicTemplateMail(
            $rendered['subject'] ?? '',
            $rendered['body'] ?? '',
            $finalAttachments
        );

        $mailer = Mail::to($to);

        if (!empty($rendered['cc'])) {
            $mailer->cc($rendered['cc']);
        }

        if (!empty($rendered['bcc'])) {
            $mailer->bcc($rendered['bcc']);
        }

        if (!empty($rendered['reply_to'])) {
            foreach ((array) $rendered['reply_to'] as $replyTo) {
                $mailable->replyTo($replyTo);
            }
        }

        if (!empty($rendered['from_email'])) {
            $mailable->from($rendered['from_email'], $rendered['from_name'] ?? null);
        }

        try {
            if ($transport === 'microsoft_graph') {
                $this->microsoftGraphMail->sendDynamicTemplateMail($to, $mailable);
            } else {
                $mailer->send($mailable);
            }
        } catch (Throwable $e) {
            if ($retryLog) {
                $retryLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'failed_at' => now(),
                ]);
            } else {
                FailedMailLog::create([
                    'template_slug' => $slug,
                    'to_email' => is_array($to) ? implode(',', $to) : $to,
                    'payload' => $data,
                    'attachments' => $finalAttachments,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'failed_at' => now(),
                    'status' => 'failed',
                    'transport' => $transport,
                ]);
            }

            Log::channel('custom_daily')->error('Mail sending failed', [
                'template_slug' => $slug,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if ($retryLog) {
            $retryLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
                'error_trace' => null,
            ]);
        }

        return true;
    }
}