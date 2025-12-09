<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\InvoiceItem;

/**
 * Observer for InvoiceItem model events.
 *
 * Handles logging for all InvoiceItem model events.
 */
final class InvoiceItemObserver extends BaseObserver
{
    /**
     * Handle the InvoiceItem "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(InvoiceItem $invoiceItem): void
    {
        $this->log('retrieved', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(InvoiceItem $invoiceItem): void
    {
        $this->log('creating', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "created" event.
     * Executed after creating (INSERT).
     */
    public function created(InvoiceItem $invoiceItem): void
    {
        $this->log('created', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(InvoiceItem $invoiceItem): void
    {
        $this->log('updating', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(InvoiceItem $invoiceItem): void
    {
        $this->log('updated', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(InvoiceItem $invoiceItem): void
    {
        $this->log('saving', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(InvoiceItem $invoiceItem): void
    {
        $this->log('saved', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(InvoiceItem $invoiceItem): void
    {
        $this->log('deleting', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(InvoiceItem $invoiceItem): void
    {
        $this->log('deleted', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(InvoiceItem $invoiceItem): void
    {
        $this->log('restoring', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(InvoiceItem $invoiceItem): void
    {
        $this->log('restored', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(InvoiceItem $invoiceItem): void
    {
        $this->log('forceDeleting', $invoiceItem);
    }

    /**
     * Handle the InvoiceItem "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(InvoiceItem $invoiceItem): void
    {
        $this->log('forceDeleted', $invoiceItem);
    }
}
