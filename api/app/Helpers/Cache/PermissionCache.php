<?php

declare(strict_types=1);

namespace App\Helpers\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

final class PermissionCache
{
    /**
     * Default TTL in seconds (10 minutes).
     */
    private const int DEFAULT_TTL = 600;

    /**
     * Cache tag for permissions.
     */
    private const string TAG = 'permissions';

    /**
     * Get an item from the cache.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::cache(self::tags())->get(
            self::key($key),
            $default
        );
    }

    /**
     * Store an item in the cache.
     */
    public static function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags())->put(
            self::key($key),
            $value,
            $ttl
        );
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        $ttl ??= config('cache.default_ttl', self::DEFAULT_TTL);

        return self::cache(self::tags())->remember(
            self::key($key),
            $ttl,
            $callback
        );
    }

    /**
     * Remove an item from the cache.
     */
    public static function forget(string $key): bool
    {
        return self::cache(self::tags())->forget(
            self::key($key)
        );
    }

    /**
     * Flush all permission cache entries.
     */
    public static function flushTags(): bool
    {
        if (self::supportsTags()) {
            return Cache::tags(self::tags())->flush();
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
     * Get a cache key for permission data.
     */
    private static function key(string $key): string
    {
        return "permissions:{$key}";
    }

    /**
     * Get cache tags for permissions.
     *
     * @return array<string>
     */
    private static function tags(): array
    {
        return [self::TAG];
    }
}
