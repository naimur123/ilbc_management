<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Shares data every Blade view in the authenticated layout needs (unread
 * notification count, current user's menu visibility) so controllers don't
 * have to repeat it. Kept separate from AppServiceProvider so it's obvious
 * where "global view data" lives.
 */
class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();

            $view->with('unreadNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
