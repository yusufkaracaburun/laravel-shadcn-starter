<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

final class ResponseCache
{
    /**
     * Default TTL in seconds (10 minutes).
     */
    private const int DEFAULT_TTL = 600;

    /**
     * Get an item from the cache.
     */
    public static function get(string $endpoint, mixed $default = null, ?int $userId = null, ?int $teamId = null): mixed
    {
        return self::cache(self::tags($endpoint, $userId, $teamId))->get(
            self::key($endpoint, $userId, $teamId),
            $default
        );
    }

    /**
     * Store an item in the cache.
     */
    public static function put(string $endpoint, mixed $value, ?int $ttl = null, ?int $userId = null, ?int $teamId = null): bool
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags($endpoint, $userId, $teamId))->put(
            self::key($endpoint, $userId, $teamId),
            $value,
            $ttl
        );
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $endpoint, Closure $callback, ?int $ttl = null, ?int $userId = null, ?int $teamId = null): mixed
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags($endpoint, $userId, $teamId))->remember(
            self::key($endpoint, $userId, $teamId),
            $ttl,
            $callback
        );
    }

    /**
     * Remove an item from the cache.
     */
    public static function forget(string $endpoint, ?int $userId = null, ?int $teamId = null): bool
    {
        return self::cache(self::tags($endpoint, $userId, $teamId))->forget(
            self::key($endpoint, $userId, $teamId)
        );
    }

    /**
     * Flush all cache entries for an endpoint.
     */
    public static function flushTags(string $endpoint): bool
    {
        if (self::supportsTags()) {
            return Cache::tags(["api:{$endpoint}"])->flush();
        }

        // If tags aren't supported, we can't flush by tag, so return true
        // In production with Redis, this will work correctly
        return true;
    }

    /**
     * Flush all cache entries for a user.
     */
    public static function flushUserTags(int $userId): bool
    {
        if (self::supportsTags()) {
            return Cache::tags(["user:{$userId}"])->flush();
        }

        // If tags aren't supported, we can't flush by tag, so return true
        // In production with Redis, this will work correctly
        return true;
    }

    /**
     * Flush all cache entries for a team.
     */
    public static function flushTeamTags(int $teamId): bool
    {
        if (self::supportsTags()) {
            return Cache::tags(["team:{$teamId}"])->flush();
        }

        // If tags aren't supported, we can't flush by tag, so return true
        // In production with Redis, this will work correctly
        return true;
    }

    /**
     * Check if the cache store supports tagging.
     */
    private static function supportsTags(): bool
    {
        return Cache::supportsTags();
    }

    /**
     * Get the cache instance with or without tags.
     */
    private static function cache(array $tags): Repository
    {
        if (self::supportsTags()) {
            return Cache::tags($tags);
        }

        return Cache::store();
    }

    /**
     * Get a cache key for API response.
     */
    private static function key(string $endpoint, ?int $userId = null, ?int $teamId = null): string
    {
        $parts = ['api', $endpoint];

        if ($userId !== null) {
            $parts[] = "user:{$userId}";
        }

        if ($teamId !== null) {
            $parts[] = "team:{$teamId}";
        }

        return implode(':', $parts);
    }

    /**
     * Get cache tags for API response.
     *
     * @return array<string>
     */
    private static function tags(string $endpoint, ?int $userId = null, ?int $teamId = null): array
    {
        $tags = ["api:{$endpoint}"];

        if ($userId !== null) {
            $tags[] = "user:{$userId}";
        }

        if ($teamId !== null) {
            $tags[] = "team:{$teamId}";
        }

        return $tags;
    }
}
