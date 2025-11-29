<?php

declare(strict_types=1);

namespace App\Observers;

use Spatie\Permission\Models\Role;
use App\Support\Cache\CacheInvalidationService;

final class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(): void
    {
        CacheInvalidationService::onPermissionsChanged();
    }
}
