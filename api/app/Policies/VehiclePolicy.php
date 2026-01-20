<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Vehicle model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class VehiclePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('vehicles.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view vehicles.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): Response
    {
        return $user->can('vehicles.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this vehicle.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('vehicles.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create vehicles.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): Response
    {
        return $user->can('vehicles.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this vehicle.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): Response
    {
        return $user->can('vehicles.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this vehicle.');
    }
}
