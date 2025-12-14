<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Customer model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class CustomerPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('customers.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view customers.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $model): Response
    {
        return $user->can('customers.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this customer.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('customers.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create customers.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $model): Response
    {
        return $user->can('customers.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this customer.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $model): Response
    {
        return $user->can('customers.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this customer.');
    }
}
