<?php

declare(strict_types=1);

namespace App\Observers;

use Spatie\Permission\Models\Permission;
use App\Helpers\Cache\CacheInvalidationService;

/**
 * Observer for Permission model events.
 *
 * Handles cache invalidation and logging for all Permission model events.
 */
final class PermissionObserver extends BaseObserver
{
    /**
     * Handle the Permission "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Permission $permission): void
    {
        $this->log('retrieved', $permission);
    }

    /**
     * Handle the Permission "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Permission $permission): void
    {
        $this->log('creating', $permission);
    }

    /**
     * Handle the Permission "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Permission $permission): void
    {
        $this->log('created', $permission);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Permission $permission): void
    {
        $this->log('updating', $permission);
    }

    /**
     * Handle the Permission "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Permission $permission): void
    {
        $this->log('updated', $permission);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Permission $permission): void
    {
        $this->log('saving', $permission);
    }

    /**
     * Handle the Permission "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Permission $permission): void
    {
        $this->log('saved', $permission);
    }

    /**
     * Handle the Permission "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Permission $permission): void
    {
        $this->log('deleting', $permission);
    }

    /**
     * Handle the Permission "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Permission $permission): void
    {
        $this->log('deleted', $permission);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Permission $permission): void
    {
        $this->log('restoring', $permission);
    }

    /**
     * Handle the Permission "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Permission $permission): void
    {
        $this->log('restored', $permission);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Permission $permission): void
    {
        $this->log('forceDeleting', $permission);
    }

    /**
     * Handle the Permission "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Permission $permission): void
    {
        $this->log('forceDeleted', $permission);
        CacheInvalidationService::onPermissionsChanged();
    }
}
