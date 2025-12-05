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
    private readonly PermissionRegistrar $permissionRegistrar;

    public function __construct()
    {
        $this->permissionRegistrar = resolve(PermissionRegistrar::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->clearCache();
        $roles = $this->getRoles();
        $this->createTestUsers($roles);
        $this->createRoleBasedUsers($roles);
        $this->clearCache();
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
        // Super admin (global role, no team)
        if (isset($roles['super-admin'])) {
            $superAdmin = $this->createUser('super-admin@example.com', 'Super Admin');
            $superAdmin->teams()->detach();
            $superAdmin->update(['current_team_id' => null]);
            $this->assignGlobalRole($superAdmin, $roles['super-admin']);
        }

        // Test users with team-scoped roles
        $team = Team::query()->first();
        if (! $team) {
            return;
        }

        $testUsers = [
            ['email' => 'test@example.com', 'name' => 'Test User', 'role' => 'customer', 'teamRole' => 'member'],
            ['email' => 'admin@example.com', 'name' => 'Admin User', 'role' => 'admin', 'teamRole' => 'owner'],
            ['email' => 'customer@example.com', 'name' => 'Customer User', 'role' => 'customer', 'teamRole' => 'member'],
            ['email' => 'contractor@example.com', 'name' => 'Contractor User', 'role' => 'contractor', 'teamRole' => 'member'],
        ];

        foreach ($testUsers as $userData) {
            if (! isset($roles[$userData['role']])) {
                continue;
            }

            $user = $this->createUser($userData['email'], $userData['name']);
            $this->assignUserToTeam($user, $team, $userData['teamRole']);
            $this->assignTeamScopedRole($user, $team, $roles[$userData['role']]);
        }
    }

    /**
     * Create additional users with different roles using factory.
     *
     * @param  array<string, Role>  $roles
     */
    private function createRoleBasedUsers(array $roles): void
    {
        $usersByRole = [
            'admin' => User::factory()->count(2)->create(),
            'customer' => User::factory()->count(50)->create(),
            'contractor' => User::factory()->count(25)->create(),
        ];

        $allUsers = collect($usersByRole)->flatten();
        $this->assignUsersToTeams($allUsers);

        $team = Team::query()->first();
        if (! $team) {
            return;
        }

        $this->clearCache();

        foreach ($usersByRole as $roleName => $users) {
            if (! isset($roles[$roleName])) {
                continue;
            }

            foreach ($users as $user) {
                if ($user->teams()->where('teams.id', $team->id)->exists()) {
                    $this->assignTeamScopedRole($user, $team, $roles[$roleName]);
                }
            }
        }

        $this->clearCache();
    }

    /**
     * Create a user with default password.
     */
    private function createUser(string $email, string $name): User
    {
        return User::query()->firstOrCreate(['email' => $email], [
            'name' => $name,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'profile_photo_path' => 'https://i.pravatar.cc/300',
        ]);
    }

    /**
     * Assign a global role to a user (team_id = null).
     */
    private function assignGlobalRole(User $user, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $this->clearCache();
        $originalTeamId = $this->permissionRegistrar->getPermissionsTeamId();
        $this->permissionRegistrar->setPermissionsTeamId(null);
        $user->assignRole($role);
        $this->permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $this->clearCache();
    }

    /**
     * Assign a team-scoped role to a user.
     */
    private function assignTeamScopedRole(User $user, Team $team, Role $role): void
    {
        if ($user->hasRole($role)) {
            return;
        }

        $this->clearCache();
        $originalTeamId = $this->permissionRegistrar->getPermissionsTeamId();
        $this->permissionRegistrar->setPermissionsTeamId($team->id);
        $user->assignRole($role);
        $this->permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $this->clearCache();
    }

    /**
     * Assign a user to a team.
     */
    private function assignUserToTeam(User $user, Team $team, string $teamRole = 'member'): void
    {
        if (! $user->teams()->where('teams.id', $team->id)->exists()) {
            $user->teams()->attach($team->id, ['role' => $teamRole]);
        }

        $user->update(['current_team_id' => $team->id]);
    }

    /**
     * Assign users to existing teams.
     *
     * @param  Collection<int, User>  $users
     */
    private function assignUsersToTeams(Collection $users): void
    {
        $team = Team::query()->first();
        if (! $team) {
            return;
        }

        foreach ($users as $user) {
            if (random_int(0, 1) !== 0) {
                if (! $user->teams()->where('teams.id', $team->id)->exists()) {
                    $user->teams()->attach($team->id, ['role' => 'member']);
                }

                if (random_int(0, 1) && ! $user->current_team_id) {
                    $user->update(['current_team_id' => $team->id]);
                }
            }
        }
    }

    /**
     * Clear permission cache.
     */
    private function clearCache(): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();
    }
}
