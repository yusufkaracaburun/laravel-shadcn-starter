<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Vehicle;
use App\Helpers\Cache\ResponseCache;

/**
 * Observer for Vehicle model events.
 *
 * Handles logging for all Vehicle model events.
 */
final class VehicleObserver extends BaseObserver
{
    /**
     * Handle the Vehicle "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Vehicle $vehicle): void
    {
        $this->log('retrieved', $vehicle);
    }

    /**
     * Handle the Vehicle "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Vehicle $vehicle): void
    {
        $this->log('creating', $vehicle);
    }

    /**
     * Handle the Vehicle "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Vehicle $vehicle): void
    {
        $this->log('created', $vehicle);
        $cache = Vehicle::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
    }

    /**
     * Handle the Vehicle "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Vehicle $vehicle): void
    {
        $this->log('updating', $vehicle);
    }

    /**
     * Handle the Vehicle "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Vehicle $vehicle): void
    {
        $this->log('updated', $vehicle);
        $cache = Vehicle::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
        ResponseCache::flushTags($cache['show'] . ".{$vehicle->id}");
    }

    /**
     * Handle the Vehicle "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Vehicle $vehicle): void
    {
        $this->log('saving', $vehicle);
    }

    /**
     * Handle the Vehicle "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Vehicle $vehicle): void
    {
        $this->log('saved', $vehicle);
    }

    /**
     * Handle the Vehicle "deleting" event.
     * Executed before deletion.
     */
    public function deleting(Vehicle $vehicle): void
    {
        $this->log('deleting', $vehicle);
    }

    /**
     * Handle the Vehicle "deleted" event.
     * Executed after deletion.
     */
    public function deleted(Vehicle $vehicle): void
    {
        $this->log('deleted', $vehicle);
        $cache = Vehicle::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
        ResponseCache::flushTags($cache['show'] . ".{$vehicle->id}");
    }

    /**
     * Handle the Vehicle "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Vehicle $vehicle): void
    {
        $this->log('restoring', $vehicle);
    }

    /**
     * Handle the Vehicle "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Vehicle $vehicle): void
    {
        $this->log('restored', $vehicle);
    }

    /**
     * Handle the Vehicle "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Vehicle $vehicle): void
    {
        $this->log('forceDeleting', $vehicle);
    }

    /**
     * Handle the Vehicle "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Vehicle $vehicle): void
    {
        $this->log('forceDeleted', $vehicle);
    }
}
