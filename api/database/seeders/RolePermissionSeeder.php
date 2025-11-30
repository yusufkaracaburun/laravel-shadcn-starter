<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = $this->createPermissions();

        // Create roles
        $roles = $this->createRoles();

        // Assign permissions to roles
        $this->assignPermissionsToRoles($roles, $permissions);

        // Clear cache after creating roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin user first (needed for team ownership)
        $admin = $this->createAdminUser($roles);

        // Create teams (owned by admin user)
        $teams = $this->createTeams($admin);

        // Assign team-scoped roles
        $this->assignTeamScopedRoles($admin, $teams, $roles);
    }

    /**
     * Create all permissions.
     *
     * @return array<string, Permission>
     */
    private function createPermissions(): array
    {
        $modules = [
            'students' => ['view', 'create', 'update', 'delete'],
            'teachers' => ['view', 'create', 'update', 'delete'],
            'attendance' => ['view', 'create', 'update', 'delete'],
            'invoices' => ['view', 'create', 'update', 'delete'],
            'teams' => ['view', 'create', 'update', 'delete', 'manage'],
            'users' => ['view', 'create', 'update', 'delete'],
        ];

        $permissions = [];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $permissions[$name] = Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }

        return $permissions;
    }

    /**
     * Create all roles.
     *
     * @return array<string, Role>
     */
    private function createRoles(): array
    {
        $roles = [
            'super-admin' => 'Super Administrator with full access',
            'admin' => 'Administrator with management access',
            'teacher' => 'Teacher with teaching permissions',
            'student' => 'Student with limited access',
            'parent' => 'Parent with viewing permissions',
        ];

        $createdRoles = [];

        foreach (array_keys($roles) as $name) {
            $createdRoles[$name] = Role::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        return $createdRoles;
    }

    /**
     * Assign permissions to roles.
     *
     * @param  array<string, Role>  $roles
     * @param  array<string, Permission>  $permissions
     */
    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        // Super Admin gets all permissions
        $roles['super-admin']->givePermissionTo(Permission::all());

        // Admin gets most permissions except super-admin specific ones
        $roles['admin']->givePermissionTo([
            $permissions['students.view'],
            $permissions['students.create'],
            $permissions['students.update'],
            $permissions['students.delete'],
            $permissions['teachers.view'],
            $permissions['teachers.create'],
            $permissions['teachers.update'],
            $permissions['teachers.delete'],
            $permissions['attendance.view'],
            $permissions['attendance.create'],
            $permissions['attendance.update'],
            $permissions['attendance.delete'],
            $permissions['invoices.view'],
            $permissions['invoices.create'],
            $permissions['invoices.update'],
            $permissions['invoices.delete'],
            $permissions['teams.view'],
            $permissions['teams.create'],
            $permissions['teams.update'],
            $permissions['teams.delete'],
            $permissions['teams.manage'],
            $permissions['users.view'],
            $permissions['users.create'],
            $permissions['users.update'],
            $permissions['users.delete'],
        ]);

        // Teacher permissions
        $roles['teacher']->givePermissionTo([
            $permissions['students.view'],
            $permissions['attendance.view'],
            $permissions['attendance.create'],
            $permissions['attendance.update'],
        ]);

        // Student permissions
        $roles['student']->givePermissionTo([
            $permissions['students.view'],
            $permissions['attendance.view'],
        ]);

        // Parent permissions
        $roles['parent']->givePermissionTo([
            $permissions['students.view'],
            $permissions['attendance.view'],
            $permissions['invoices.view'],
        ]);
    }

    /**
     * Create admin user.
     *
     * @param  array<string, Role>  $roles
     */
    private function createAdminUser(array $roles): User
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->refresh();

        if (isset($roles['super-admin']) && ! $admin->hasRole($roles['super-admin'])) {
            $this->assignGlobalRole($admin, $roles['super-admin']);
        }

        return $admin;
    }

    /**
     * Create default teams.
     *
     * @return array<Team>
     */
    private function createTeams(User $admin): array
    {
        return [Team::query()->firstOrCreate(['name' => 'Default Team'], [
            'user_id' => $admin->id,
            'personal_team' => false,
        ])];
    }

    /**
     * Assign team-scoped roles.
     *
     * @param  array<Team>  $teams
     * @param  array<string, Role>  $roles
     */
    private function assignTeamScopedRoles(User $admin, array $teams, array $roles): void
    {
        if ($teams === [] || ! isset($roles['admin'])) {
            return;
        }

        $team = $teams[0];

        if (! $admin->teams()->where('teams.id', $team->id)->exists()) {
            $admin->teams()->attach($team->id, ['role' => 'owner']);
        }

        $admin->update(['current_team_id' => $team->id]);
        $this->assignTeamScopedRole($admin, $team, $roles['admin']);
    }

    /**
     * Assign a global role to a user (team_id = null).
     */
    private function assignGlobalRole(User $user, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null);
        $user->assignRole($role);
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }

    /**
     * Assign a team-scoped role to a user.
     */
    private function assignTeamScopedRole(User $user, Team $team, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($team->id);
        $user->assignRole($role);
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();
    }
}
