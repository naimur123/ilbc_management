<?php

namespace App\Policies;

use App\Models\Request as WorkRequest;
use App\Models\User;

/**
 * Object-level rule on top of the request.* permission strings: whether a
 * given user may see/act on THIS particular request, once they already
 * hold the base permission (checked separately via middleware/@can on the
 * permission string itself).
 */
class RequestPolicy
{
    public function view(User $user, WorkRequest $request): bool
    {
        if ($user->can('request.view_all')) {
            return true;
        }

        if ($user->can('request.view_own')) {
            return $request->created_by === $user->id
                || $request->salesperson?->user_id === $user->id;
        }

        return false;
    }

    public function update(User $user, WorkRequest $request): bool
    {
        if (! $user->can('request.edit')) {
            return false;
        }

        // Rule: closed/cancelled requests are never directly editable — only via Reopen (Section 27).
        return ! in_array($request->status, ['CLOSED', 'CANCELLED'], true);
    }

    public function delete(User $user, WorkRequest $request): bool
    {
        return $user->can('request.delete') && $request->status === 'DRAFT';
    }

    public function cancel(User $user, WorkRequest $request): bool
    {
        return $user->can('request.cancel') && ! in_array($request->status, ['CLOSED', 'CANCELLED'], true);
    }

    public function reopen(User $user, WorkRequest $request): bool
    {
        return $user->can('request.reopen') && $request->status === 'CLOSED';
    }
}
