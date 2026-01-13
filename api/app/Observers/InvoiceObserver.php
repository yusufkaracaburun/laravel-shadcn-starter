<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;

/**
 * Observer for Invoice model events.
 *
 * Handles logging for all Invoice model events.
 */
final class InvoiceObserver extends BaseObserver
{
    /**
     * Handle the Invoice "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Invoice $invoice): void
    {
        $this->log('retrieved', $invoice);
    }

    /**
     * Handle the Invoice "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Invoice $invoice): void
    {
        $this->log('creating', $invoice);
    }

    /**
     * Handle the Invoice "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Invoice $invoice): void
    {
        $this->log('created', $invoice);
    }

    /**
     * Handle the Invoice "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Invoice $invoice): void
    {
        $this->log('updating', $invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Invoice $invoice): void
    {
        $this->log('updating', $invoice);
    }

    /**
     * Handle the Invoice "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Invoice $invoice): void
    {
        $this->log('saving', $invoice);
    }

    /**
     * Handle the Invoice "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Invoice $invoice): void
    {
        $this->log('saved', $invoice);
    }

    /**
     * Handle the Invoice "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Invoice $invoice): void
    {
        $this->log('deleting', $invoice);
    }

    /**
     * Handle the Invoice "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->log('deleted', $invoice);
    }

    /**
     * Handle the Invoice "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Invoice $invoice): void
    {
        $this->log('restoring', $invoice);
    }

    /**
     * Handle the Invoice "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Invoice $invoice): void
    {
        $this->log('restored', $invoice);
    }

    /**
     * Handle the Invoice "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Invoice $invoice): void
    {
        $this->log('forceDeleting', $invoice);
    }

    /**
     * Handle the Invoice "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Invoice $invoice): void
    {
        $this->log('forceDeleted', $invoice);
    }
}
