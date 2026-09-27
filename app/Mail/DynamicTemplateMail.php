<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;
    protected string $bodyHtml;
    protected array $emailAttachments;

    public function __construct(string $subject, string $body, array $emailAttachments = [])
    {
        $this->subjectLine = $subject;
        $this->bodyHtml = $body;
        $this->emailAttachments = $emailAttachments;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
                     ->html($this->bodyHtml);

        foreach ($this->emailAttachments as $att) {
            $path = storage_path('app/public/' . $att['path']);

            if (file_exists($path)) {
                $mail = $mail->attach($path, [
                    'as' => $att['name'] ?? basename($att['path']),
                    'mime' => $att['mime'] ?? null,
                ]);

                Log::channel('custom_daily')->info('Attachment check', [
                    'path' => $path,
                    'name' => $att['name'] ?? null
                ]);
            } else {
                Log::channel('custom_daily')->error('Attachment missing', [
                    'path' => $path,
                    'name' => $att['name'] ?? null
                ]);
            }
        }

        return $mail;
    }

    public function getSubjectLine(): string
    {
        return $this->subjectLine;
    }

    public function getBodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function getAttachmentsData(): array
    {
        return $this->emailAttachments;
    }
}