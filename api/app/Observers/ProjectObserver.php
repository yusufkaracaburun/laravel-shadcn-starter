<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Project;

/**
 * Observer for Project model events.
 *
 * Handles logging for all Project model events.
 */
final class ProjectObserver extends BaseObserver
{
    /**
     * Handle the Project "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Project $model): void
    {
        $this->log('retrieved', $model);
    }

    /**
     * Handle the Project "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Project $model): void
    {
        $this->log('creating', $model);
    }

    /**
     * Handle the Project "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Project $model): void
    {
        $this->log('created', $model);
    }

    /**
     * Handle the Project "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Project $model): void
    {
        $this->log('updating', $model);
    }

    /**
     * Handle the Project "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Project $model): void
    {
        $this->log('updated', $model);
    }

    /**
     * Handle the Project "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Project $model): void
    {
        $this->log('saving', $model);
    }

    /**
     * Handle the Project "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Project $model): void
    {
        $this->log('saved', $model);
    }

    /**
     * Handle the Project "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Project $model): void
    {
        $this->log('deleting', $model);
    }

    /**
     * Handle the Project "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Project $model): void
    {
        $this->log('deleted', $model);
    }

    /**
     * Handle the Project "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Project $model): void
    {
        $this->log('restoring', $model);
    }

    /**
     * Handle the Project "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Project $model): void
    {
        $this->log('restored', $model);
    }

    /**
     * Handle the Project "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Project $model): void
    {
        $this->log('forceDeleting', $model);
    }

    /**
     * Handle the Project "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Project $model): void
    {
        $this->log('forceDeleted', $model);
    }
}
