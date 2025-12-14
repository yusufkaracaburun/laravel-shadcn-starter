<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy for User model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('users.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view users.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        // Allow if it's the same user
        if ($user->id === $model->id) {
            return Response::allow();
        }

        // Allow if users belong to the same team and user has permission
        if ($this->belongsToSameTeam($user, $model) && $user->can('users.view')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this user.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('users.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create users.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        // Allow if it's the same user
        if ($user->id === $model->id) {
            return Response::allow();
        }

        // Allow if users belong to the same team and user has permission
        if ($this->belongsToSameTeam($user, $model) && $user->can('users.update')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this user.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        // Prevent self-deletion
        if ($user->id === $model->id) {
            return Response::deny('You cannot delete yourself.');
        }

        // Only allow if users belong to the same team and user has permission
        if ($this->belongsToSameTeam($user, $model) && $user->can('users.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to delete this user.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        // Only allow if users belong to the same team and user has permission
        if ($this->belongsToSameTeam($user, $model) && $user->can('users.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to restore this user.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        // Prevent self-deletion
        if ($user->id === $model->id) {
            return Response::deny('You cannot permanently delete yourself.');
        }

        // Only allow if users belong to the same team and user has permission
        if ($this->belongsToSameTeam($user, $model) && $user->can('users.delete')) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to permanently delete this user.');
    }

    /**
     * Check if both users belong to the same team.
     */
    private function belongsToSameTeam(User $user, User $model): bool
    {
        // Both must have a current team
        if (! $user->current_team_id || ! $model->current_team_id) {
            return false;
        }

        // Check if they have the same current team
        if ($user->current_team_id === $model->current_team_id) {
            return true;
        }

        // Also check if they both belong to any common team
        return $user->teams()
            ->whereIn('teams.id', $model->teams()->pluck('teams.id'))
            ->exists();
    }
}
