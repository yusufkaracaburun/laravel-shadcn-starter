<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

/**
 * Policy for Permission model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class PermissionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('permissions.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view permissions.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): Response
    {
        return $user->can('permissions.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this permission.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('permissions.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create permissions.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): Response
    {
        return $user->can('permissions.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this permission.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): Response
    {
        return $user->can('permissions.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this permission.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): Response
    {
        return $user->can('permissions.create')
            ? Response::allow()
            : Response::deny('You do not have permission to restore permissions.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): Response
    {
        return $user->can('permissions.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete permissions.');
    }
}
