<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;

/**
 * Observer for Product model events.
 *
 * Handles logging for all Product model events.
 */
final class ProductObserver extends BaseObserver
{
    /**
     * Handle the Product "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Product $model): void
    {
        $this->log('retrieved', $model);
    }

    /**
     * Handle the Product "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Product $model): void
    {
        $this->log('creating', $model);
    }

    /**
     * Handle the Product "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Product $model): void
    {
        $this->log('created', $model);
    }

    /**
     * Handle the Product "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Product $model): void
    {
        $this->log('updating', $model);
    }

    /**
     * Handle the Product "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Product $model): void
    {
        $this->log('updated', $model);
    }

    /**
     * Handle the Product "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Product $model): void
    {
        $this->log('saving', $model);
    }

    /**
     * Handle the Product "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Product $model): void
    {
        $this->log('saved', $model);
    }

    /**
     * Handle the Product "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Product $model): void
    {
        $this->log('deleting', $model);
    }

    /**
     * Handle the Product "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Product $model): void
    {
        $this->log('deleted', $model);
    }

    /**
     * Handle the Product "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Product $model): void
    {
        $this->log('restoring', $model);
    }

    /**
     * Handle the Product "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Product $model): void
    {
        $this->log('restored', $model);
    }

    /**
     * Handle the Product "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Product $model): void
    {
        $this->log('forceDeleting', $model);
    }

    /**
     * Handle the Product "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Product $model): void
    {
        $this->log('forceDeleted', $model);
    }
}
