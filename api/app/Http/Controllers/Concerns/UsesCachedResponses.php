<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Closure;
use Illuminate\Http\Request;
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

        // Include query parameters in cache key
        $request = request();
        $queryParams = $this->getCacheableQueryParams($request);

        // Append query params hash to endpoint for unique cache keys
        if (!empty($queryParams)) {
            $queryHash = ResponseCache::hashQueryParams($queryParams);
            $endpoint .= ":query:{$queryHash}";
        }

        return ResponseCache::remember($endpoint, $callback, $ttl, $userId, $teamId);
    }

    /**
     * Get cacheable query parameters from request.
     *
     * Only include parameters that affect the query result.
     * Override in controller if you need different behavior.
     *
     * @return array<string, mixed>
     */
    protected function getCacheableQueryParams(Request $request): array
    {
        $params = [];

        // Only include parameters that affect the query result
        foreach (['filter', 'sort', 'include', 'fields', 'page', 'per_page'] as $key) {
            if ($request->has($key)) {
                $params[$key] = $request->input($key);
            }
        }

        return $params;
    }

    /**
     * Invalidate cached response for an endpoint.
     */
    protected function invalidateCachedResponse(string $endpoint): void
    {
        ResponseCache::flushTags($endpoint);
    }
}
