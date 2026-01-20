<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Equipment;
use App\Helpers\Cache\ResponseCache;

/**
 * Observer for Equipment model events.
 *
 * Handles logging for all Equipment model events.
 */
final class EquipmentObserver extends BaseObserver
{
    /**
     * Handle the Equipment "retrieved" event.
     * Executed when an existing model is retrieved from the database.
     */
    public function retrieved(Equipment $equipment): void
    {
        $this->log('retrieved', $equipment);
    }

    /**
     * Handle the Equipment "creating" event.
     * Executed before creating (INSERT).
     */
    public function creating(Equipment $equipment): void
    {
        $this->log('creating', $equipment);
    }

    /**
     * Handle the Equipment "created" event.
     * Executed after creating (INSERT).
     */
    public function created(Equipment $equipment): void
    {
        $this->log('created', $equipment);
        $cache = Equipment::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
    }

    /**
     * Handle the Equipment "updating" event.
     * Executed before updating (UPDATE).
     */
    public function updating(Equipment $equipment): void
    {
        $this->log('updating', $equipment);
    }

    /**
     * Handle the Equipment "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(Equipment $equipment): void
    {
        $this->log('updated', $equipment);
        $cache = Equipment::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
        ResponseCache::flushTags($cache['show'] . ".{$equipment->id}");
    }

    /**
     * Handle the Equipment "saving" event.
     * Executed before saving (both create and update).
     */
    public function saving(Equipment $equipment): void
    {
        $this->log('saving', $equipment);
    }

    /**
     * Handle the Equipment "saved" event.
     * Executed after saving (both create and update).
     */
    public function saved(Equipment $equipment): void
    {
        $this->log('saved', $equipment);
    }

    /**
     * Handle the Equipment "deleting" event.
     * Executed before deletion.
     */
    public function deleting(Equipment $equipment): void
    {
        $this->log('deleting', $equipment);
    }

    /**
     * Handle the Equipment "deleted" event.
     * Executed after deletion.
     */
    public function deleted(Equipment $equipment): void
    {
        $this->log('deleted', $equipment);
        $cache = Equipment::getCacheKeys();
        ResponseCache::flushTags($cache['index']);
        ResponseCache::flushTags($cache['show'] . ".{$equipment->id}");
    }

    /**
     * Handle the Equipment "restoring" event.
     * Executed before restoring (soft-delete → active).
     */
    public function restoring(Equipment $equipment): void
    {
        $this->log('restoring', $equipment);
    }

    /**
     * Handle the Equipment "restored" event.
     * Executed after restoring (soft-delete → active).
     */
    public function restored(Equipment $equipment): void
    {
        $this->log('restored', $equipment);
    }

    /**
     * Handle the Equipment "forceDeleting" event.
     * Executed before permanent deletion (force delete).
     */
    public function forceDeleting(Equipment $equipment): void
    {
        $this->log('forceDeleting', $equipment);
    }

    /**
     * Handle the Equipment "forceDeleted" event.
     * Executed after permanent deletion (force delete).
     */
    public function forceDeleted(Equipment $equipment): void
    {
        $this->log('forceDeleted', $equipment);
    }
}
