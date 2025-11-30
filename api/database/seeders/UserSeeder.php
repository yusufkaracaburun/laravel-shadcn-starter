<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Get roles (they should already exist from RolePermissionSeeder)
        $roles = $this->getRoles();

        // Create specific test users
        $this->createTestUsers($roles);

        // Create additional users with different roles
        $this->createRoleBasedUsers($roles);

        // Clear cache after creating users
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Get all roles from the database.
     *
     * @return array<string, Role>
     */
    private function getRoles(): array
    {
        $roleNames = ['super-admin', 'admin', 'customer', 'contractor'];
        $roles = [];

        foreach ($roleNames as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $roles[$roleName] = $role;
            }
        }

        return $roles;
    }

    /**
     * Create specific test users with known credentials.
     *
     * @param  array<string, Role>  $roles
     */
    private function createTestUsers(array $roles): void
    {
        // Test user (already created in DatabaseSeeder, but ensure it exists)
        $testUser = User::query()->firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (! $testUser->hasAnyRole($roles)) {
            $testUser->assignRole($roles['customer'] ?? null);
        }

        // Admin test user
        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (! $admin->hasRole($roles['admin'] ?? null)) {
            $admin->assignRole($roles['admin'] ?? null);
        }

        // Customer test user
        $customer = User::query()->firstOrCreate(['email' => 'customer@example.com'], [
            'name' => 'Customer User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (! $customer->hasRole($roles['customer'] ?? null)) {
            $customer->assignRole($roles['customer'] ?? null);
        }

        // Contractor test user
        $contractor = User::query()->firstOrCreate(['email' => 'contractor@example.com'], [
            'name' => 'Contractor User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (! $contractor->hasRole($roles['contractor'] ?? null)) {
            $contractor->assignRole($roles['contractor'] ?? null);
        }
    }

    /**
     * Create additional users with different roles using factory.
     *
     * @param  array<string, Role>  $roles
     */
    private function createRoleBasedUsers(array $roles): void
    {
        // Create additional admins
        $admins = User::factory()->count(2)->create();
        foreach ($admins as $admin) {
            if (! $admin->hasRole($roles['admin'] ?? null)) {
                $admin->assignRole($roles['admin'] ?? null);
            }
        }

        // Create additional customers
        $customers = User::factory()->count(10)->create();
        foreach ($customers as $customer) {
            if (! $customer->hasRole($roles['customer'] ?? null)) {
                $customer->assignRole($roles['customer'] ?? null);
            }
        }

        // Create additional contractors
        $contractors = User::factory()->count(5)->create();
        foreach ($contractors as $contractor) {
            if (! $contractor->hasRole($roles['contractor'] ?? null)) {
                $contractor->assignRole($roles['contractor'] ?? null);
            }
        }

        // Assign some users to teams if teams exist
        $this->assignUsersToTeams($admins->merge($customers)->merge($contractors));
    }

    /**
     * Assign users to existing teams.
     *
     * @param  Collection<int, User>  $users
     */
    private function assignUsersToTeams($users): void
    {
        $teams = Team::all();

        if ($teams->isEmpty()) {
            return;
        }

        $defaultTeam = $teams->first();

        foreach ($users as $user) {
            // Randomly assign some users to teams
            if (random_int(0, 1) !== 0) {
                if (! $user->teams()->where('teams.id', $defaultTeam->id)->exists()) {
                    $user->teams()->attach($defaultTeam->id, ['role' => 'member']);
                }

                // Set as current team for some users
                if (random_int(0, 1) && ! $user->current_team_id) {
                    $user->update(['current_team_id' => $defaultTeam->id]);
                }
            }
        }
    }
}
