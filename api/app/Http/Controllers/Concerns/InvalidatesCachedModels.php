<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Support\Cache\CacheInvalidationService;

trait InvalidatesCachedModels
{
    /**
     * Invalidate caches after a model is created.
     */
    protected function invalidateAfterCreate(string $modelType, ?int $teamId = null): void
    {
        match ($modelType) {
            'team' => $teamId && CacheInvalidationService::onTeamUpdated($teamId),
            default => null,
        };
    }

    /**
     * Invalidate caches after a model is updated.
     */
    protected function invalidateAfterUpdate(string $modelType, ?int $teamId = null): void
    {
        match ($modelType) {
            'team' => $teamId && CacheInvalidationService::onTeamUpdated($teamId),
            default => null,
        };
    }

    /**
     * Invalidate caches after a model is deleted.
     */
    protected function invalidateAfterDelete(string $modelType, ?int $teamId = null): void
    {
        match ($modelType) {
            'team' => $teamId && CacheInvalidationService::onTeamDeleted($teamId),
            default => null,
        };
    }
}
