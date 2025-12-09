<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Team model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class TeamPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any teams.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('teams.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view teams.');
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): Response
    {
        // User can view if they own the team, belong to it, or have teams.view permission
        if ($team->user_id === $user->id) {
            return Response::allow();
        }

        if ($user->teams()->where('teams.id', $team->id)->exists()) {
            return Response::allow();
        }

        return $user->can('teams.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this team.');
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): Response
    {
        return $user->can('teams.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create teams.');
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): Response
    {
        // Only owner or users with teams.update permission can update
        if ($team->user_id === $user->id || $user->can('teams.update')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this team.');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): Response
    {
        // Only owner or users with teams.delete permission can delete
        if ($team->user_id === $user->id || $user->can('teams.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to delete this team.');
    }

    /**
     * Determine whether the user can manage the team.
     */
    public function manage(User $user, Team $team): Response
    {
        // Only owner or users with teams.manage permission can manage
        if ($team->user_id === $user->id || $user->can('teams.manage')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to manage this team.');
    }
}
