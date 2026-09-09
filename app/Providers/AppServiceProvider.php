<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Section 38: HTTPS enforcement in production. Left off in
        // local/testing so the installer still works over plain HTTP
        // during initial cPanel setup before SSL/AutoSSL is issued.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
