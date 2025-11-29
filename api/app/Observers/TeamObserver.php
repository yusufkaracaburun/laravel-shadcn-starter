<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Team;
use App\Support\Cache\CacheInvalidationService;

final class TeamObserver
{
    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        // Invalidate team cache when team is updated
        CacheInvalidationService::onTeamUpdated($team->id);
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        // Invalidate team cache when team is deleted
        CacheInvalidationService::onTeamDeleted($team->id);
    }
}
