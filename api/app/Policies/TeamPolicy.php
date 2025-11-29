<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

/**
 * Policy for Team model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class TeamPolicy
{
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
        return $team->user_id === $user->id
            || $user->teams()->where('teams.id', $team->id)->exists()
            || $user->can('teams.view');
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
