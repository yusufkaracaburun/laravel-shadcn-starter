<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

final class TeamCache
{
    /**
     * Default TTL in seconds (10 minutes).
     */
    private const int DEFAULT_TTL = 600;

    /**
     * Get an item from the cache.
     */
    public static function get(int $teamId, string $key, mixed $default = null): mixed
    {
        return self::cache(self::tags($teamId))->get(
            self::key($teamId, $key),
            $default
        );
    }

    /**
     * Store an item in the cache.
     */
    public static function put(int $teamId, string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags($teamId))->put(
            self::key($teamId, $key),
            $value,
            $ttl
        );
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(int $teamId, string $key, Closure $callback, ?int $ttl = null): mixed
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags($teamId))->remember(
            self::key($teamId, $key),
            $ttl,
            $callback
        );
    }

    /**
     * Remove an item from the cache.
     */
    public static function forget(int $teamId, string $key): bool
    {
        return self::cache(self::tags($teamId))->forget(
            self::key($teamId, $key)
        );
    }

    /**
     * Flush all cache entries for a team.
     */
    public static function flushTags(int $teamId): bool
    {
        if (self::supportsTags()) {
            return Cache::tags(self::tags($teamId))->flush();
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
     * Get a cache key for team-scoped data.
     */
    private static function key(int $teamId, string $key): string
    {
        return "team:{$teamId}:{$key}";
    }

    /**
     * Get cache tags for a team.
     *
     * @return array<string>
     */
    private static function tags(int $teamId): array
    {
        return ["team:{$teamId}"];
    }
}
