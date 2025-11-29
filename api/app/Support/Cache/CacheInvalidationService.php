<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Spatie\Permission\PermissionRegistrar;

final class CacheInvalidationService
{
    /**
     * Invalidate all caches for a team.
     */
    public static function invalidateTeam(int $teamId): void
    {
        TeamCache::flushTags($teamId);
        ResponseCache::flushTeamTags($teamId);
    }

    /**
     * Invalidate all caches for a user.
     */
    public static function invalidateUser(int $userId): void
    {
        ResponseCache::flushUserTags($userId);
    }

    /**
     * Invalidate permission caches and reset Spatie Permission cache.
     */
    public static function invalidatePermissions(): void
    {
        PermissionCache::flushTags();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Invalidate API response cache for a specific endpoint.
     */
    public static function invalidateApiResponse(string $endpoint): void
    {
        ResponseCache::flushTags($endpoint);
    }

    /**
     * Invalidate all caches when a team is updated.
     */
    public static function onTeamUpdated(int $teamId): void
    {
        self::invalidateTeam($teamId);
    }

    /**
     * Invalidate all caches when a team is deleted.
     */
    public static function onTeamDeleted(int $teamId): void
    {
        self::invalidateTeam($teamId);
    }

    /**
     * Invalidate caches when permissions are changed.
     */
    public static function onPermissionsChanged(): void
    {
        self::invalidatePermissions();
    }

    /**
     * Invalidate caches when a role is assigned to a user.
     */
    public static function onRoleAssigned(?int $teamId = null): void
    {
        self::invalidatePermissions();

        if ($teamId !== null) {
            self::invalidateTeam($teamId);
        }
    }

    /**
     * Invalidate caches when a user switches teams.
     */
    public static function onTeamSwitched(int $userId, int $oldTeamId, int $newTeamId): void
    {
        self::invalidateUser($userId);
        self::invalidateTeam($oldTeamId);
        self::invalidateTeam($newTeamId);
    }
}
