<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * One generic notification shape for every event in Section 35's list
 * (request submitted, billing approval needed, loading assigned, ...).
 * Only the database channel is wired today; adding 'mail' (and later
 * whatsapp/teams channel classes) is a one-line change to via() once
 * those integrations exist — NotificationService callers never change.
 */
class WorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?int $requestId = null,
        public ?string $url = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'request_id' => $this->requestId,
            'url' => $this->url,
        ];
    }
}
