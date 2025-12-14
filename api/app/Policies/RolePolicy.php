<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Role model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class RolePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('roles.view')
            ? Response::allow()
            : Response::deny('You are not allowed to view roles.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): Response
    {
        return $user->can('roles.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this role.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('roles.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create roles.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): Response
    {
        // Prevent updating system roles
        if ($role->is_system) {
            return Response::deny('System roles cannot be updated.');
        }

        return $user->can('roles.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this role.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): Response
    {
        // Prevent deleting system roles
        if ($role->is_system) {
            return Response::deny('System roles cannot be deleted.');
        }

        return $user->can('roles.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this role.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): Response
    {
        return $user->can('roles.create')
            ? Response::allow()
            : Response::deny('You do not have permission to restore roles.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): Response
    {
        // Prevent force deleting system roles
        if ($role->is_system) {
            return Response::deny('System roles cannot be permanently deleted.');
        }

        return $user->can('roles.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete roles.');
    }
}
