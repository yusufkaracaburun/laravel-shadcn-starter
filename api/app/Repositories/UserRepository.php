<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Support\Cache\TeamCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * User Repository
 *
 * Note: This class is not marked as final to allow mocking in unit tests.
 */
class UserRepository
{
    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     *
     * @param  array<string>  $allowedFilters
     * @param  array<string>  $allowedSorts
     * @param  array<string>  $allowedIncludes
     * @return LengthAwarePaginator<User>
     */
    public function getPaginated(
        int $perPage,
        ?int $teamId = null,
        array $allowedFilters = ['name', 'email'],
        array $allowedSorts = ['id', 'name', 'email', 'created_at'],
        array $allowedIncludes = ['teams', 'currentTeam', 'ownedTeams', 'roles']
    ): LengthAwarePaginator {
        $query = User::query();

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))
                ->orWhereHas('ownedTeams', fn ($query) => $query->where('id', $teamId));
        }

        // Build query with QueryBuilder for filtering, sorting, and includes
        $queryBuilder = QueryBuilder::for($query)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->allowedIncludes($allowedIncludes);

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): User
    {
        $user = User::query()->findOrFail($userId);

        if ($teamId === null) {
            $user->load(['teams', 'currentTeam', 'ownedTeams', 'roles']);

            return $user;
        }

        return TeamCache::remember(
            $teamId,
            "users:{$userId}",
            fn () => $user->load(['teams', 'currentTeam', 'ownedTeams', 'roles'])
        );
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function create(array $data, ?int $teamId = null): User
    {
        // Remove profile_photo and role from data before creating user
        // Media Library handles file uploads separately
        // Role is handled separately with team context
        $role = $data['role'] ?? null;
        unset($data['profile_photo'], $data['role']);

        $user = User::query()->create($data);
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        // Assign role if provided and not empty
        if ($role && $role !== '') {
            $this->assignRole($user, $role, $teamId);
        }

        // Load roles after assignment (if any)
        $user->load('roles');

        return $user;
    }

    /**
     * Update a user.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function update(User $user, array $data, ?int $teamId = null): User
    {
        // Remove profile_photo and role from data before updating user
        // Media Library handles file uploads separately
        // Role is handled separately with team context
        $role = $data['role'] ?? null;
        unset($data['profile_photo'], $data['role']);

        $user->update($data);
        $user->refresh();
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        // Update role if provided
        if ($role !== null && $role !== '') {
            $this->assignRole($user, $role, $teamId);
        }

        // Load roles after assignment (if any)
        $user->load('roles');

        return $user;
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): User
    {
        $user->refresh();
        $user->load(['teams', 'currentTeam', 'ownedTeams', 'roles']);

        return $user;
    }

    /**
     * Assign a role to a user with team context.
     */
    private function assignRole(User $user, ?string $roleName, ?int $teamId = null): void
    {
        if (! $roleName || $roleName === '') {
            return;
        }

        $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

        if (! $role) {
            return;
        }

        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($teamId);

        try {
            // Sync roles (remove existing roles and assign new one)
            $user->syncRoles([$role]);
        } finally {
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);
            $permissionRegistrar->forgetCachedPermissions();
        }
    }
}
