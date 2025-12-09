<?php

declare(strict_types=1);

namespace App\Observers;

use Spatie\Permission\Models\Role;
use App\Helpers\Cache\CacheInvalidationService;

/**
 * Observer for Role model events.
 *
 * Handles cache invalidation and logging for all Role model events.
 */
final class RoleObserver extends BaseObserver
{
    /**
     * Handle the Role "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Role $role): void
    {
        $this->log('retrieved', $role);
    }

    /**
     * Handle the Role "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Role $role): void
    {
        $this->log('creating', $role);
    }

    /**
     * Handle the Role "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Role $role): void
    {
        $this->log('created', $role);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Role $role): void
    {
        $this->log('updating', $role);
    }

    /**
     * Handle the Role "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Role $role): void
    {
        $this->log('updated', $role);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Role $role): void
    {
        $this->log('saving', $role);
    }

    /**
     * Handle the Role "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Role $role): void
    {
        $this->log('saved', $role);
    }

    /**
     * Handle the Role "deleting" event.
     * Executed before soft delete.
     */
    public function deleting(Role $role): void
    {
        $this->log('deleting', $role);
    }

    /**
     * Handle the Role "deleted" event.
     * Executed after soft delete.
     */
    public function deleted(Role $role): void
    {
        $this->log('deleted', $role);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Role $role): void
    {
        $this->log('restoring', $role);
    }

    /**
     * Handle the Role "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Role $role): void
    {
        $this->log('restored', $role);
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Role $role): void
    {
        $this->log('forceDeleting', $role);
    }

    /**
     * Handle the Role "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Role $role): void
    {
        $this->log('forceDeleted', $role);
        CacheInvalidationService::onPermissionsChanged();
    }
}
