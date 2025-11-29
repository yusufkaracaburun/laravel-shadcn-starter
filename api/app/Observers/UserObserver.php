<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Support\Cache\CacheInvalidationService;

final class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Invalidate user cache when user is updated
        CacheInvalidationService::invalidateUser($user->id);

        // If current_team_id changed, invalidate both old and new team caches
        if ($user->wasChanged('current_team_id')) {
            $oldTeamId = $user->getOriginal('current_team_id');
            $newTeamId = $user->getAttributeValue('current_team_id');

            if ($oldTeamId !== null) {
                CacheInvalidationService::invalidateTeam($oldTeamId);
            }

            if ($newTeamId !== null) {
                CacheInvalidationService::invalidateTeam($newTeamId);
            }
        } else {
            $teamId = $user->getAttributeValue('current_team_id');
            if ($teamId !== null) {
                CacheInvalidationService::invalidateTeam($teamId);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Invalidate user cache when user is deleted
        CacheInvalidationService::invalidateUser($user->id);

        $teamId = $user->getAttributeValue('current_team_id');
        if ($teamId !== null) {
            CacheInvalidationService::invalidateTeam($teamId);
        }
    }
}
