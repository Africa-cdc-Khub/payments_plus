<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\RegistrationRepositoryInterface;
use App\Contracts\InvitationServiceInterface;
use App\Repositories\RegistrationRepository;
use App\Services\InvitationService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RegistrationRepositoryInterface::class, RegistrationRepository::class);
        $this->app->bind(InvitationServiceInterface::class, InvitationService::class);
    }

    public function boot(): void
    {
        //
    }
}

