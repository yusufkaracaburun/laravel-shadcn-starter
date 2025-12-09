<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Item;

/**
 * Observer for Item model events.
 *
 * Handles logging for all Item model events.
 */
final class ItemObserver extends BaseObserver
{
    /**
     * Handle the Item "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Item $model): void
    {
        $this->log('retrieved', $model);
    }

    /**
     * Handle the Item "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Item $model): void
    {
        $this->log('creating', $model);
    }

    /**
     * Handle the Item "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Item $model): void
    {
        $this->log('created', $model);
    }

    /**
     * Handle the Item "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Item $model): void
    {
        $this->log('updating', $model);
    }

    /**
     * Handle the Item "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Item $model): void
    {
        $this->log('updated', $model);
    }

    /**
     * Handle the Item "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Item $model): void
    {
        $this->log('saving', $model);
    }

    /**
     * Handle the Item "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Item $model): void
    {
        $this->log('saved', $model);
    }

    /**
     * Handle the Item "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Item $model): void
    {
        $this->log('deleting', $model);
    }

    /**
     * Handle the Item "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Item $model): void
    {
        $this->log('deleted', $model);
    }

    /**
     * Handle the Item "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Item $model): void
    {
        $this->log('restoring', $model);
    }

    /**
     * Handle the Item "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Item $model): void
    {
        $this->log('restored', $model);
    }

    /**
     * Handle the Item "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Item $model): void
    {
        $this->log('forceDeleting', $model);
    }

    /**
     * Handle the Item "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Item $model): void
    {
        $this->log('forceDeleted', $model);
    }
}
