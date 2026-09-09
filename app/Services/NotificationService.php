<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Support\Collection;

/**
 * Thin wrapper around Laravel's Notification facade (Section 35). Every
 * event listed in the master prompt ("Request submitted", "Billing
 * approval needed", ...) should call notify() with the permission string
 * that identifies who needs to see it — this resolves the actual users at
 * send time so nothing is ever hard-coded to a specific person (Section 3).
 */
class NotificationService
{
    public function notifyPermission(string $permission, string $title, string $body, ?int $requestId = null, ?string $url = null): void
    {
        $users = User::permission($permission)->where('is_active', true)->get();

        $this->send($users, $title, $body, $requestId, $url);
    }

    public function notifyUser(User $user, string $title, string $body, ?int $requestId = null, ?string $url = null): void
    {
        $user->notify(new WorkflowNotification($title, $body, $requestId, $url));
    }

    /**
     * @param  Collection<int, User>  $users
     */
    public function send(Collection $users, string $title, string $body, ?int $requestId = null, ?string $url = null): void
    {
        foreach ($users as $user) {
            $user->notify(new WorkflowNotification($title, $body, $requestId, $url));
        }
    }
}
