<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Helpers\Cache\ResponseCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class CacheApiResponse
{
    /**
     * Routes that should not be cached.
     *
     * @var array<string>
     */
    private const array EXCLUDED_ROUTES = [
        'api.user.current',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for excluded routes
        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return $next($request);
        }

        // Get user and team context
        $user = Auth::user();
        $userId = $user?->id;
        $teamId = $user?->current_team_id;

        // Build cache key from route name or URI
        $endpoint = $routeName ?? $request->path();

        // Try to get cached response
        $cachedResponse = ResponseCache::get($endpoint, null, $userId, $teamId);

        if ($cachedResponse !== null) {
            return response()->json($cachedResponse);
        }

        // Execute the request
        $response = $next($request);

        // Only cache successful responses
        if ($response->isSuccessful() && $response instanceof JsonResponse) {
            $responseData = $response->getData(true);

            // Cache the response data
            ResponseCache::put($endpoint, $responseData, null, $userId, $teamId);
        }

        return $response;
    }
}
