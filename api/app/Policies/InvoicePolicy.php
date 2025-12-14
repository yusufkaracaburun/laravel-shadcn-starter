<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\Response;

/**
 * Policy for Invoice model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 */
final class InvoicePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): Response
    {
        return $user->can('invoices.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view invoices.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $model): Response
    {
        return $user->can('invoices.view')
            ? Response::allow()
            : Response::deny('You do not have permission to view this invoice.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('invoices.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create invoices.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $model): Response
    {
        return $user->can('invoices.update')
            ? Response::allow()
            : Response::deny('You do not have permission to update this invoice.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $model): Response
    {
        return $user->can('invoices.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this invoice.');
    }
}
