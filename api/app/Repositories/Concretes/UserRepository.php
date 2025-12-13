<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Helpers\Cache\TeamCache;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UserRepository extends QueryableRepository implements UserRepositoryInterface
{
    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator
    {
        // Start with base query
        $query = User::query();

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))
                ->orWhereHas('ownedTeams', fn ($query) => $query->where('id', $teamId));
        }

        // Get the original request and create a modified version with our per_page
        $originalRequest = request();
        $queryParams = $originalRequest->query->all();
        $queryParams['per_page'] = (string) $perPage;
        $queryParams['page'] = (string) ($originalRequest->input('page', 1));

        // Build URL with query string
        $url = $originalRequest->url();
        $queryString = http_build_query($queryParams);
        $fullUrl = $queryString !== '' && $queryString !== '0' ? $url.'?'.$queryString : $url;

        // Create a new request with modified query parameters
        $modifiedRequest = Request::create(
            $fullUrl,
            $originalRequest->method(),
            $originalRequest->request->all(),
            $originalRequest->cookies->all(),
            $originalRequest->files->all(),
            $originalRequest->server->all(),
            $originalRequest->getContent()
        );

        // Build query with QueryBuilder for filtering, sorting, and includes
        $queryRequest = QueryBuilderRequest::fromRequest($modifiedRequest);
        $queryBuilder = QueryBuilder::for($query, $queryRequest)
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedIncludes($this->getAllowedIncludes());

        // Get the underlying Eloquent builder after QueryBuilder has applied filters/sorts/includes
        // Then paginate directly with our per_page value
        $eloquentBuilder = $queryBuilder->getEloquentBuilder();

        // Get the page number from the modified request
        $page = (int) $modifiedRequest->input('page', 1);

        // Paginate directly on the Eloquent builder
        $paginated = $eloquentBuilder->paginate($perPage, ['*'], 'page', $page);

        // Load roles correctly for each user with proper team context
        $paginated->getCollection()->transform($this->loadUserRelationships(...));

        return $paginated;
    }

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): User
    {
        $user = User::query()->findOrFail($userId);

        if ($teamId === null) {
            $this->loadUserRelationships($user);
            return $user;
        }

        return TeamCache::remember(
            $teamId,
            "users:{$userId}",
            fn (): \App\Models\User => $this->loadUserRelationships($user)
        );
    }

    /**
     * Create a new user with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function createUser(array $data, ?int $teamId = null): User
    {
        // Remove profile_photo and role from data before creating user
        // Media Library handles file uploads separately
        // Role is handled separately with team context
        $role = $data['role'] ?? null;
        unset($data['profile_photo'], $data['role']);

        $user = User::query()->create($data);

        // Assign role if provided and not empty
        if ($role && $role !== '') {
            $this->assignRole($user, $role, $teamId);
        }

        // Load relationships with proper team context for roles
        return $this->loadUserRelationships($user);
    }

    /**
     * Update a user by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function updateUser(User $user, array $data, ?int $teamId = null): User
    {
        // Remove profile_photo and role from data before updating user
        // Media Library handles file uploads separately
        // Role is handled separately with team context
        $role = $data['role'] ?? null;
        unset($data['profile_photo'], $data['role']);

        $user->update($data);
        $user->refresh();

        // Update role if provided
        if ($role !== null && $role !== '') {
            $this->assignRole($user, $role, $teamId);
        }

        // Load relationships with proper team context for roles
        return $this->loadUserRelationships($user);
    }

    /**
     * Delete a user by model instance.
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): User
    {
        $user->refresh();
        $this->loadUserRelationships($user);

        return $user;
    }

    /**
     * Get all verified users.
     *
     * @return Collection<int, User>
     */
    public function getVerifiedUsers(): Collection
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->get();
    }

    /**
     * Get all active users.
     *
     * @return Collection<int, User>
     */
    public function getActiveUsers(): Collection
    {
        return $this->getUsersByStatus(UserStatus::ACTIVE);
    }

    /**
     * Get users by status.
     *
     * @return Collection<int, User>
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): Collection
    {
        $statusEnum = $status instanceof UserStatus ? $status : UserStatus::from($status);

        return User::query()
            ->where('status', $statusEnum->value)
            ->get();
    }

    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('email'),
            AllowedFilter::scope('created_at'),
        ];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return ['id', 'name', 'email', 'created_at'];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return ['teams', 'currentTeam', 'ownedTeams', 'roles'];
    }

    /**
     * Model binding.
     */
    protected function model(): string
    {
        return User::class;
    }

    /**
     * Load user relationships with proper team context for roles.
     */
    private function loadUserRelationships(User $user): User
    {
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();

        // Load non-role relationships first
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        // Clear permission cache to ensure fresh role queries
        $permissionRegistrar->forgetCachedPermissions();
        $user->unsetRelation('roles');

        // Load roles with proper team context
        // First, load global roles (team_id = null) for users like super-admin
        $permissionRegistrar->setPermissionsTeamId(null);
        $globalRoles = $user->roles()->get();

        // Then, load team-scoped roles from all teams the user belongs to
        $teamScopedRoles = collect();
        
        // Get all unique team IDs the user belongs to (from teams and ownedTeams)
        $allTeamIds = $user->teams->pluck('id')
            ->merge($user->ownedTeams->pluck('id'))
            ->unique()
            ->filter()
            ->values();

        // Load roles for each team the user belongs to
        foreach ($allTeamIds as $teamId) {
            $permissionRegistrar->setPermissionsTeamId($teamId);
            $permissionRegistrar->forgetCachedPermissions();
            $user->unsetRelation('roles');
            $teamRoles = $user->roles()->get();
            $teamScopedRoles = $teamScopedRoles->merge($teamRoles);
        }

        // Merge global and team-scoped roles, removing duplicates
        $allRoles = $globalRoles->merge($teamScopedRoles)->unique('id')->values();
        $user->setRelation('roles', $allRoles);

        // Restore original team context
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
        $permissionRegistrar->forgetCachedPermissions();

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
