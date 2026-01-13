<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Concretes\RoleRepository;
use App\Repositories\Concretes\TeamRepository;
use App\Repositories\Concretes\UserRepository;
use App\Repositories\Concretes\InvoiceRepository;
use App\Repositories\Concretes\PaymentRepository;
use App\Repositories\Concretes\ProductRepository;
use App\Repositories\Concretes\ProjectRepository;
use App\Repositories\Concretes\VehicleRepository;
use App\Repositories\Concretes\CustomerRepository;
use App\Repositories\Concretes\EquipmentRepository;
use App\Repositories\Concretes\TestFinalRepository;
use App\Repositories\Concretes\PermissionRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use App\Repositories\Contracts\TestFinalRepositoryInterface;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(TestFinalRepositoryInterface::class, TestFinalRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(EquipmentRepositoryInterface::class, EquipmentRepository::class);
    }
}
