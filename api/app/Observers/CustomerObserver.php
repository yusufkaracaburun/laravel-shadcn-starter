<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Customer;

/**
 * Observer for Customer model events.
 *
 * Handles logging for all Customer model events.
 */
final class CustomerObserver extends BaseObserver
{
    /**
     * Handle the Customer "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Customer $customer): void
    {
        $this->log('retrieved', $customer);
    }

    /**
     * Handle the Customer "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Customer $customer): void
    {
        $this->log('creating', $customer);
    }

    /**
     * Handle the Customer "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Customer $customer): void
    {
        $this->log('created', $customer);
    }

    /**
     * Handle the Customer "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Customer $customer): void
    {
        $this->log('updating', $customer);
    }

    /**
     * Handle the Customer "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Customer $customer): void
    {
        $this->log('updated', $customer);
    }

    /**
     * Handle the Customer "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Customer $customer): void
    {
        $this->log('saving', $customer);
    }

    /**
     * Handle the Customer "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Customer $customer): void
    {
        $this->log('saved', $customer);
    }

    /**
     * Handle the Customer "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Customer $customer): void
    {
        $this->log('deleting', $customer);
    }

    /**
     * Handle the Customer "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Customer $customer): void
    {
        $this->log('deleted', $customer);
    }

    /**
     * Handle the Customer "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Customer $customer): void
    {
        $this->log('restoring', $customer);
    }

    /**
     * Handle the Customer "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Customer $customer): void
    {
        $this->log('restored', $customer);
    }

    /**
     * Handle the Customer "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Customer $customer): void
    {
        $this->log('forceDeleting', $customer);
    }

    /**
     * Handle the Customer "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Customer $customer): void
    {
        $this->log('forceDeleted', $customer);
    }
}
