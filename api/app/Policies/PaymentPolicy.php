<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Payment model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class PaymentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('payments.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view payments.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $model): Response
    {
        return $user->can('payments.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this payment.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('payments.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create payments.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $model): Response
    {
        return $user->can('payments.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this payment.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $model): Response
    {
        return $user->can('payments.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this payment.');
    }
}
