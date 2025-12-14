<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Concretes\TeamService;
use App\Services\Concretes\UserService;
use Illuminate\Support\ServiceProvider;
use App\Services\Concretes\CompanyService;
use App\Services\Concretes\ProjectService;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Contracts\CompanyServiceInterface;
use App\Services\Contracts\ProjectServiceInterface;

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
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
    }
}
