<?php

namespace App\Providers;

use Cphia2025\ExchangeEmailService;
use Cphia2025\ExchangeOAuth;
use Illuminate\Support\ServiceProvider;

/**
 * Enhanced Exchange Email Service Provider for Laravel
 * 
 * Registers Exchange email services using the existing working classes from parent src/
 * 
 * @version 2.0.0
 */
class ExchangeEmailServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register()
    {
        // Register the email service as singleton
        $this->app->singleton('exchange.email', function ($app) {
            return new ExchangeEmailService([
                'tenant_id' => config('exchange-email.tenant_id'),
                'client_id' => config('exchange-email.client_id'),
                'client_secret' => config('exchange-email.client_secret'),
                'redirect_uri' => config('exchange-email.redirect_uri'),
                'scope' => config('exchange-email.scope'),
                'from_email' => config('exchange-email.from_email'),
                'from_name' => config('exchange-email.from_name'),
                'auth_method' => config('exchange-email.auth_method'),
                'debug' => config('exchange-email.debug'),
            ]);
        });

        // Register the OAuth handler as singleton
        $this->app->singleton('exchange.oauth', function ($app) {
            return new ExchangeOAuth(
                config('exchange-email.tenant_id'),
                config('exchange-email.client_id'),
                config('exchange-email.client_secret'),
                config('exchange-email.redirect_uri'),
                config('exchange-email.scope'),
                config('exchange-email.auth_method')
            );
        });

        // Merge configuration
        $this->mergeConfigFrom(
            config_path('exchange-email.php'), 'exchange-email'
        );
    }

    /**
     * Bootstrap services
     */
    public function boot()
    {
        // Load views if they exist
        if (is_dir(resource_path('views/emails'))) {
            $this->loadViewsFrom(resource_path('views/emails'), 'exchange-email');
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides()
    {
        return [
            'exchange.email',
            'exchange.oauth',
        ];
    }
}
