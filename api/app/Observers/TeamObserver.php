<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Team;
use App\Helpers\Cache\CacheInvalidationService;

/**
 * Observer for Team model events.
 *
 * Handles cache invalidation and logging for all Team model events.
 */
final class TeamObserver extends BaseObserver
{
    /**
     * Handle the Team "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Team $team): void
    {
        $this->log('retrieved', $team);
    }

    /**
     * Handle the Team "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Team $team): void
    {
        $this->log('creating', $team);
    }

    /**
     * Handle the Team "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Team $team): void
    {
        $this->log('created', $team);
    }

    /**
     * Handle the Team "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Team $team): void
    {
        $this->log('updating', $team);
    }

    /**
     * Handle the Team "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Team $team): void
    {
        $this->log('updated', $team);
        // Invalidate team cache when team is updated
        CacheInvalidationService::onTeamUpdated($team->id);
    }

    /**
     * Handle the Team "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Team $team): void
    {
        $this->log('saving', $team);
    }

    /**
     * Handle the Team "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Team $team): void
    {
        $this->log('saved', $team);
    }

    /**
     * Handle the Team "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Team $team): void
    {
        $this->log('deleting', $team);
    }

    /**
     * Handle the Team "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Team $team): void
    {
        $this->log('deleted', $team);
        // Invalidate team cache when team is deleted
        CacheInvalidationService::onTeamDeleted($team->id);
    }

    /**
     * Handle the Team "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Team $team): void
    {
        $this->log('restoring', $team);
    }

    /**
     * Handle the Team "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Team $team): void
    {
        $this->log('restored', $team);
        // Invalidate team cache when team is restored
        CacheInvalidationService::onTeamUpdated($team->id);
    }

    /**
     * Handle the Team "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Team $team): void
    {
        $this->log('forceDeleting', $team);
    }

    /**
     * Handle the Team "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Team $team): void
    {
        $this->log('forceDeleted', $team);
        // Invalidate team cache when team is permanently deleted
        CacheInvalidationService::onTeamDeleted($team->id);
    }
}
