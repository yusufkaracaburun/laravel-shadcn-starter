<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\Policies\PermissionPolicy;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    private array $policies = [
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->configureSuperAdmin();
    }

    /**
     * Register the application's policies.
     */
    private function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Configure super admin to have all permissions.
     * Implicitly grant "Super Admin" role all permissions via Gate::before.
     * This works in the app by using gate-related functions like auth()->user->can() and @can().
     *
     * The super admin check is performed with team context cleared to check for global roles,
     * ensuring super admins have access regardless of team context.
     */
    private function configureSuperAdmin(): void
    {
        // @phpstan-ignore-next-line - $ability parameter is required by Gate::before signature but unused
        Gate::before(function ($user, string $_ability): ?true {
            // Check for super-admin role with team context cleared (global role)
            if (! $user) {
                return null;
            }

            $permissionRegistrar = resolve(PermissionRegistrar::class);
            $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

            $isSuperAdmin = $user->hasRole('super-admin');

            // Restore original team context
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);

            return $isSuperAdmin ? true : null;
        });
    }
}
