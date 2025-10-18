<?php

namespace AgabaandreOffice365\ExchangeEmailService;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Enhanced Exchange Email Service Provider
 * 
 * Laravel service provider for the Enhanced Exchange Email Service
 * - Multiple authentication methods
 * - Background refresh support
 * - Configuration publishing
 * - Artisan commands
 * - Route registration
 * 
 * @author SendMail ExchangeEmailService
 * @version 2.0.0
 */
class ExchangeEmailServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register()
    {
        // Register the email service
        $this->app->singleton('exchange.email', function ($app) {
            return new ExchangeEmailService();
        });

        // Register the OAuth handler
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

        // Register the token refresh job
        $this->app->singleton('exchange.token.refresh', function ($app) {
            return new TokenRefreshJob([
                'tenant_id' => config('exchange-email.tenant_id'),
                'client_id' => config('exchange-email.client_id'),
                'client_secret' => config('exchange-email.client_secret'),
                'redirect_uri' => config('exchange-email.redirect_uri'),
                'scope' => config('exchange-email.scope'),
                'auth_method' => config('exchange-email.auth_method'),
                'log_file' => config('exchange-email.log_file'),
                'max_log_size' => config('exchange-email.max_log_size')
            ]);
        });

        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/exchange-email.php', 'exchange-email');
    }

    /**
     * Bootstrap services
     */
    public function boot()
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/exchange-email.php' => config_path('exchange-email.php'),
            ], 'exchange-email-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'exchange-email-migrations');

            // Register Artisan commands (if they exist)
            // $this->commands([
            //     \AgabaandreOffice365\ExchangeEmailService\Console\ExchangeEmailTestCommand::class,
            //     \AgabaandreOffice365\ExchangeEmailService\Console\TokenRefreshCommand::class,
            //     \AgabaandreOffice365\ExchangeEmailService\Console\ServiceStatusCommand::class,
            // ]);
        }

        // Register routes
        $this->registerRoutes();

        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'exchange-email');
    }

    /**
     * Register routes
     */
    protected function registerRoutes()
    {
        Route::group([
            'prefix' => 'exchange-email',
            'namespace' => 'AgabaandreOffice365\ExchangeEmailService\Http\Controllers',
            'middleware' => ['web']
        ], function () {
            // OAuth callback route
            Route::get('oauth/callback', 'OAuthController@callback')->name('exchange-email.oauth.callback');
            
            // OAuth authorization route
            Route::get('oauth/authorize', 'OAuthController@authorize')->name('exchange-email.oauth.authorize');
            
            // Service status route
            Route::get('status', 'StatusController@index')->name('exchange-email.status');
            
            // Test email route
            Route::post('test', 'TestController@send')->name('exchange-email.test');
            
            // Token refresh route
            Route::post('refresh', 'TokenController@refresh')->name('exchange-email.refresh');
        });
    }

    /**
     * Get the services provided by the provider
     */
    public function provides()
    {
        return [
            'exchange.email',
            'exchange.oauth',
            'exchange.token.refresh',
        ];
    }
}
