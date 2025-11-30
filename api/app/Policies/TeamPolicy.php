<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Policy for Team model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class TeamPolicy
{
    /**
     * Perform pre-authorization checks on the model.
     * Super admin bypasses all authorization checks.
     */
    public function before(User $user): ?bool
    {
        // Check for super-admin with team context cleared (global role)
        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

        // Clear permission cache and roles relation to ensure fresh check
        $permissionRegistrar->forgetCachedPermissions();

        $user->unsetRelation('roles');

        // Load roles fresh from database
        $user->load('roles');

        $isSuperAdmin = $user->hasRole('super-admin');

        // Restore original team context
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);

        if ($isSuperAdmin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any teams.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('teams.view');
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        // User can view if they own the team, belong to it, or have teams.view permission
        if ($team->user_id === $user->id) {
            return true;
        }

        if ($user->teams()->where('teams.id', $team->id)->exists()) {
            return true;
        }

        return $user->can('teams.view');
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return $user->can('teams.create');
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        // Only owner or users with teams.update permission can update
        return $team->user_id === $user->id || $user->can('teams.update');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        // Only owner or users with teams.delete permission can delete
        return $team->user_id === $user->id || $user->can('teams.delete');
    }

    /**
     * Determine whether the user can manage the team.
     */
    public function manage(User $user, Team $team): bool
    {
        // Only owner or users with teams.manage permission can manage
        return $team->user_id === $user->id || $user->can('teams.manage');
    }
}
