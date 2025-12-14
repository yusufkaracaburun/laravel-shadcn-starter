<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Helpers\Cache\CacheInvalidationService;

/**
 * Observer for User model events.
 *
 * Handles cache invalidation and logging for all User model events.
 */
final class UserObserver extends BaseObserver
{
    /**
     * Handle the User "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(User $user): void
    {
        $this->log('retrieved', $user);
    }

    /**
     * Handle the User "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(User $user): void
    {
        $this->log('creating', $user);
    }

    /**
     * Handle the User "created" event.
     * Executed after creating (INSERT).
     */
    public function created(User $user): void
    {
        $this->log('created', $user);
    }

    /**
     * Handle the User "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(User $user): void
    {
        $this->log('updating', $user);
    }

    /**
     * Handle the User "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(User $user): void
    {
        $this->log('updated', $user);

        // Invalidate user cache when user is updated
        CacheInvalidationService::invalidateUser($user->id);

        // If current_team_id changed, invalidate both old and new team caches
        if ($user->wasChanged('current_team_id')) {
            $oldTeamId = $user->getOriginal('current_team_id');
            $newTeamId = $user->getAttributeValue('current_team_id');

            if ($oldTeamId !== null) {
                CacheInvalidationService::invalidateTeam($oldTeamId);
            }

            if ($newTeamId !== null) {
                CacheInvalidationService::invalidateTeam($newTeamId);
            }
        } else {
            $teamId = $user->getAttributeValue('current_team_id');
            if ($teamId !== null) {
                CacheInvalidationService::invalidateTeam($teamId);
            }
        }
    }

    /**
     * Handle the User "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(User $user): void
    {
        $this->log('saving', $user);
    }

    /**
     * Handle the User "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(User $user): void
    {
        $this->log('saved', $user);
    }

    /**
     * Handle the User "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(User $user): void
    {
        $this->log('deleting', $user);
    }

    /**
     * Handle the User "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(User $user): void
    {
        $this->log('deleted', $user);

        // Invalidate user cache when user is deleted
        CacheInvalidationService::invalidateUser($user->id);

        $teamId = $user->getAttributeValue('current_team_id');
        if ($teamId !== null) {
            CacheInvalidationService::invalidateTeam($teamId);
        }
    }

    /**
     * Handle the User "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(User $user): void
    {
        $this->log('restoring', $user);
    }

    /**
     * Handle the User "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(User $user): void
    {
        $this->log('restored', $user);

        // Invalidate user cache when user is restored
        CacheInvalidationService::invalidateUser($user->id);

        $teamId = $user->getAttributeValue('current_team_id');
        if ($teamId !== null) {
            CacheInvalidationService::invalidateTeam($teamId);
        }
    }

    /**
     * Handle the User "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(User $user): void
    {
        $this->log('forceDeleting', $user);
    }

    /**
     * Handle the User "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(User $user): void
    {
        $this->log('forceDeleted', $user);

        // Invalidate user cache when user is permanently deleted
        CacheInvalidationService::invalidateUser($user->id);

        $teamId = $user->getAttributeValue('current_team_id');
        if ($teamId !== null) {
            CacheInvalidationService::invalidateTeam($teamId);
        }
    }
}
