<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Grant all permissions & access to Store Owner / Admin automatically
        Gate::before(function ($user, $ability) {
            if (isset($user->role) && in_array(strtolower($user->role), ['owner', 'admin', 'store admin'])) {
                return true;
            }

            if (method_exists($user, 'hasRole') && $user->hasRole('owner')) {
                return true;
            }
        });
    }
}
