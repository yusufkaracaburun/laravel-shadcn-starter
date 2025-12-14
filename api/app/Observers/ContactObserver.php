<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Contact;

/**
 * Observer for Contact model events.
 *
 * Handles logging for all Contact model events.
 */
final class ContactObserver extends BaseObserver
{
    /**
     * Handle the Contact "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Contact $contact): void
    {
        $this->log('retrieved', $contact);
    }

    /**
     * Handle the Contact "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Contact $contact): void
    {
        $this->log('creating', $contact);
    }

    /**
     * Handle the Contact "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Contact $contact): void
    {
        $this->log('created', $contact);
    }

    /**
     * Handle the Contact "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Contact $contact): void
    {
        $this->log('updating', $contact);
    }

    /**
     * Handle the Contact "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Contact $contact): void
    {
        $this->log('updated', $contact);
    }

    /**
     * Handle the Contact "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Contact $contact): void
    {
        $this->log('saving', $contact);
    }

    /**
     * Handle the Contact "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Contact $contact): void
    {
        $this->log('saved', $contact);
    }

    /**
     * Handle the Contact "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Contact $contact): void
    {
        $this->log('deleting', $contact);
    }

    /**
     * Handle the Contact "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Contact $contact): void
    {
        $this->log('deleted', $contact);
    }

    /**
     * Handle the Contact "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Contact $contact): void
    {
        $this->log('restoring', $contact);
    }

    /**
     * Handle the Contact "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Contact $contact): void
    {
        $this->log('restored', $contact);
    }

    /**
     * Handle the Contact "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Contact $contact): void
    {
        $this->log('forceDeleting', $contact);
    }

    /**
     * Handle the Contact "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Contact $contact): void
    {
        $this->log('forceDeleted', $contact);
    }
}
