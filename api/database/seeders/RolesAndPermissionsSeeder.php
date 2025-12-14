<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Enums\UserRole;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (UserRole::permissions() as $permission) {
            collect($permission)->each(fn ($permission) => Permission::query()->firstOrCreate(['name' => $permission]));
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'slug' => str(UserRole::SUPER_ADMIN->value)->slug(), 'is_system' => true]);
        $superAdmin->syncPermissions(UserRole::SUPER_ADMIN->rolePermissions());

        $adminRole = Role::query()->firstOrCreate(['name' => UserRole::ADMIN->value, 'slug' => str(UserRole::ADMIN->value)->slug(), 'is_system' => true]);
        $adminRole->syncPermissions(UserRole::ADMIN->rolePermissions());

        $managerRole = Role::query()->firstOrCreate(['name' => UserRole::MANAGER->value, 'slug' => str(UserRole::MANAGER->value)->slug(), 'is_system' => true]);
        $managerRole->syncPermissions(UserRole::MANAGER->rolePermissions());

        $userRole = Role::query()->firstOrCreate(['name' => UserRole::USER->value, 'slug' => str(UserRole::USER->value)->slug(), 'is_system' => true]);
        $userRole->syncPermissions(UserRole::USER->rolePermissions());

        $customerRole = Role::query()->firstOrCreate(['name' => UserRole::CUSTOMER->value, 'slug' => str(UserRole::CUSTOMER->value)->slug(), 'is_system' => true]);
        $customerRole->syncPermissions(UserRole::CUSTOMER->rolePermissions());
    }
}
