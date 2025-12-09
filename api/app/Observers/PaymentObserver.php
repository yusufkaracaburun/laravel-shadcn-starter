<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Payment;

/**
 * Observer for Payment model events.
 *
 * Handles logging for all Payment model events.
 */
final class PaymentObserver extends BaseObserver
{
    /**
     * Handle the Payment "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Payment $payment): void
    {
        $this->log('retrieved', $payment);
    }

    /**
     * Handle the Payment "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Payment $payment): void
    {
        $this->log('creating', $payment);
    }

    /**
     * Handle the Payment "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Payment $payment): void
    {
        $this->log('created', $payment);
    }

    /**
     * Handle the Payment "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Payment $payment): void
    {
        $this->log('updating', $payment);
    }

    /**
     * Handle the Payment "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Payment $payment): void
    {
        $this->log('updated', $payment);
    }

    /**
     * Handle the Payment "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Payment $payment): void
    {
        $this->log('saving', $payment);
    }

    /**
     * Handle the Payment "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Payment $payment): void
    {
        $this->log('saved', $payment);
    }

    /**
     * Handle the Payment "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Payment $payment): void
    {
        $this->log('deleting', $payment);
    }

    /**
     * Handle the Payment "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Payment $payment): void
    {
        $this->log('deleted', $payment);
    }

    /**
     * Handle the Payment "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Payment $payment): void
    {
        $this->log('restoring', $payment);
    }

    /**
     * Handle the Payment "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Payment $payment): void
    {
        $this->log('restored', $payment);
    }

    /**
     * Handle the Payment "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Payment $payment): void
    {
        $this->log('forceDeleting', $payment);
    }

    /**
     * Handle the Payment "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Payment $payment): void
    {
        $this->log('forceDeleted', $payment);
    }
}
