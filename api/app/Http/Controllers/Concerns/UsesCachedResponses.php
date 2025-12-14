<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Closure;
use App\Helpers\Cache\ResponseCache;
use Illuminate\Support\Facades\Auth;

trait UsesCachedResponses
{
    /**
     * Get a cached response or execute callback and cache the result.
     */
    protected function cachedResponse(string $endpoint, Closure $callback, ?int $ttl = null): mixed
    {
        $user = Auth::user();
        $userId = $user?->id;
        $teamId = null;

        if ($user) {
            // Use getAttributeValue to safely get the attribute without throwing exception
            $teamId = $user->getAttributeValue('current_team_id');
        }

        return ResponseCache::remember($endpoint, $callback, $ttl, $userId, $teamId);
    }

    /**
     * Invalidate cached response for an endpoint.
     */
    protected function invalidateCachedResponse(string $endpoint): void
    {
        ResponseCache::flushTags($endpoint);
    }
}
