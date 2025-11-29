<?php

declare(strict_types=1);

namespace App\Observers;

use Spatie\Permission\Models\Permission;
use App\Support\Cache\CacheInvalidationService;

final class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }
}
