<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Equipment;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Equipment model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class EquipmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('equipments.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view equipment.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Equipment $equipment): Response
    {
        return $user->can('equipments.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this equipment.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('equipments.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create equipment.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Equipment $equipment): Response
    {
        return $user->can('equipments.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this equipment.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Equipment $equipment): Response
    {
        return $user->can('equipments.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this equipment.');
    }
}
