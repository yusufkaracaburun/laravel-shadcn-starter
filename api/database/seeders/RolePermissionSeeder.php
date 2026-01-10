<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
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

        $this->createRoles(UserRole::SUPER_ADMIN);
        $this->createRoles(UserRole::ADMIN);
        $this->createRoles(UserRole::USER);
        $this->createRoles(UserRole::CUSTOMER);
        $this->createRoles(UserRole::CONTRACTOR);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = $this->createAdminUser(UserRole::values());
        $teams = $this->createTeams($admin);
        $this->assignTeamScopedRoles($admin, $teams, UserRole::values());
    }

    private function createRoles(UserRole $user_role): void
    {
        $role = Role::query()->firstOrCreate([
            'name'       => $user_role->value,
            'slug'       => str($user_role->value)->slug(),
            'guard_name' => 'web',
            'is_system'  => true,
        ]);
        $role->syncPermissions($user_role->rolePermissions());
    }

    private function createAdminUser(array $roles): User
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name'              => 'Admin User',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->refresh();

        if (isset($roles['super-admin']) && !$admin->hasRole($roles['super-admin'])) {
            $this->assignGlobalRole($admin, $roles['super-admin']);
        }

        return $admin;
    }

    private function createTeams(User $admin): array
    {
        return [Team::query()->firstOrCreate(['name' => 'Default Team'], [
            'user_id'       => $admin->id,
            'personal_team' => false,
        ])];
    }

    private function assignTeamScopedRoles(User $admin, array $teams, array $roles): void
    {
        if ($teams === [] || !isset($roles['admin'])) {
            return;
        }

        $team = $teams[0];

        if (!$admin->teams()->where('teams.id', $team->id)->exists()) {
            $admin->teams()->attach($team->id, ['role' => 'owner']);
        }

        $admin->update(['current_team_id' => $team->id]);
        $this->assignTeamScopedRole($admin, $team, $roles['admin']);
    }

    private function assignGlobalRole(User $user, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null);
        $user->assignRole($role);
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }

    private function assignTeamScopedRole(User $user, Team $team, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($team->id);
        $user->assignRole($role);
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }
}
