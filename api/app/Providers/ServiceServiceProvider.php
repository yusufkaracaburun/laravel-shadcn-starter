<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Concretes\RoleService;
use App\Services\Concretes\TeamService;
use App\Services\Concretes\UserService;
use Illuminate\Support\ServiceProvider;
use App\Services\Concretes\InvoiceService;
use App\Services\Concretes\PaymentService;
use App\Services\Concretes\ProductService;
use App\Services\Concretes\ProjectService;
use App\Services\Concretes\CustomerService;
use App\Services\Concretes\PermissionService;
use App\Services\Contracts\RoleServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Contracts\ProjectServiceInterface;
use App\Services\Contracts\CustomerServiceInterface;
use App\Services\Contracts\PermissionServiceInterface;

final class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
    }
}
