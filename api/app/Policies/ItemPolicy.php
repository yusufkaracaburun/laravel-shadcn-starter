<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Item;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Item model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class ItemPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('items.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view items.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Item $model): Response
    {
        return $user->can('items.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this item.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('items.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create items.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $model): Response
    {
        return $user->can('items.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this item.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $model): Response
    {
        return $user->can('items.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this item.');
    }
}

