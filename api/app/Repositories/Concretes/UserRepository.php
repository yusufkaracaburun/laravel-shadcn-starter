<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Helpers\Cache\TeamCache;
use Spatie\Permission\Models\Role;
use App\Models\Team;
use App\Filters\UserTeamFilter;
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
    public function query(): QueryBuilder
    {
        return parent::query();
    }


    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            ['name', 'email', 'status']
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('name'),
                AllowedFilter::exact('email'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('team_id', new UserTeamFilter()),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return ['teams', 'currentTeam', 'ownedTeams', 'roles'];
    }

    public function getCurrentUser(User $user): User
    {
        $user->refresh();
        $this->loadUserRelationships($user);

        return $user;
    }

    public function getVerifiedUsers(): Collection
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->get();
    }

    public function getActiveUsers(): Collection
    {
        return $this->getUsersByStatus(UserStatus::ACTIVE);
    }

    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): Collection
    {
        $statusEnum = $status instanceof UserStatus ? $status : UserStatus::from($status);

        return User::query()
            ->where('status', $statusEnum->value)
            ->get();
    }

    public function getAllFiltered(): Collection
    {
        return $this->getFiltered();
    }

    public function getVerifiedFiltered(): Collection
    {
        return $this->query()
            ->whereNotNull('email_verified_at')
            ->get();
    }

    protected function model(): string
    {
        return User::class;
    }

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
}
