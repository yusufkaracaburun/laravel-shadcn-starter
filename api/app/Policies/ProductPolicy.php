<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\Response;

/**
 * Policy for product model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class ProductPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('products.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view products.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $model): Response
    {
        return $user->can('products.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this product.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('products.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create products.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $model): Response
    {
        return $user->can('products.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this product.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $model): Response
    {
        return $user->can('products.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this product.');
    }
}
