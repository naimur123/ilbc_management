<?php

namespace App\Providers;

use App\Models\Request as WorkRequest;
use App\Models\Vendor;
use App\Policies\RequestPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        WorkRequest::class => RequestPolicy::class,
        Vendor::class => VendorPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin and Administrator always pass every permission/policy check
        // (Section 4: "Super Admin"), without needing every permission explicitly assigned.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole(['Super Admin', 'Administrator']) ? true : null;
        });
    }
}
