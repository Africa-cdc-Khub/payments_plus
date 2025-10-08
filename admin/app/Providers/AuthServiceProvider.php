<?php

namespace App\Providers;

use App\Models\Registration;
use App\Models\Payment;
use App\Policies\RegistrationPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Registration::class => RegistrationPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Before hook: Admin role has all permissions
        Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\Admin && $user->role === 'admin') {
                return true;
            }
        });

        // Define custom gates for admin guard
        Gate::define('manage-admins', function ($user) {
            return $user instanceof \App\Models\Admin && $user->role === 'admin';
        });

        Gate::define('manage-packages', function ($user) {
            return $user instanceof \App\Models\Admin && $user->role === 'admin';
        });

        Gate::define('view-dashboard', function ($user) {
            return $user instanceof \App\Models\Admin && 
                   in_array($user->role, ['admin', 'secretariat', 'finance']);
        });

        Gate::define('view-executive-dashboard', function ($user) {
            return $user instanceof \App\Models\Admin && $user->role === 'executive';
        });
    }
}

