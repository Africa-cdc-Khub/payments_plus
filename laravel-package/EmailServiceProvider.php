<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cphia2025\LaravelEmailService;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LaravelEmailService::class, function ($app) {
            return new LaravelEmailService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
