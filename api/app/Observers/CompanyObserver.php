<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Company;

/**
 * Observer for Company model events.
 *
 * Handles logging for all Company model events.
 */
final class CompanyObserver extends BaseObserver
{
    /**
     * Handle the Company "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Company $model): void
    {
        $this->log('retrieved', $model);
    }

    /**
     * Handle the Company "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Company $model): void
    {
        $this->log('creating', $model);
    }

    /**
     * Handle the Company "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Company $model): void
    {
        $this->log('created', $model);
    }

    /**
     * Handle the Company "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Company $model): void
    {
        $this->log('updating', $model);
    }

    /**
     * Handle the Company "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Company $model): void
    {
        $this->log('updated', $model);
    }

    /**
     * Handle the Company "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Company $model): void
    {
        $this->log('saving', $model);
    }

    /**
     * Handle the Company "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Company $model): void
    {
        $this->log('saved', $model);
    }

    /**
     * Handle the Company "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Company $model): void
    {
        $this->log('deleting', $model);
    }

    /**
     * Handle the Company "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Company $model): void
    {
        $this->log('deleted', $model);
    }

    /**
     * Handle the Company "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Company $model): void
    {
        $this->log('restoring', $model);
    }

    /**
     * Handle the Company "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Company $model): void
    {
        $this->log('restored', $model);
    }

    /**
     * Handle the Company "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Company $model): void
    {
        $this->log('forceDeleting', $model);
    }

    /**
     * Handle the Company "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Company $model): void
    {
        $this->log('forceDeleted', $model);
    }
}
