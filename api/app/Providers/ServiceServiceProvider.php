<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Concretes\TeamService;
use App\Services\Concretes\UserService;
use App\Services\Concretes\CompanyService;
use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Contracts\CompanyServiceInterface;

final class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
    }
}
