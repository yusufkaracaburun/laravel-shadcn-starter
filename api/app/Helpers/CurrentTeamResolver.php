<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

final class CurrentTeamResolver implements PermissionsTeamResolver
{
    /**
     * Explicitly set team ID (can be overridden by middleware).
     */
    private int|string|null $teamId = null;

    /**
     * Get the team ID for the current request.
     * Returns explicitly set team ID, or falls back to authenticated user's current_team_id.
     */
    public function getPermissionsTeamId(): int|string|null
    {
        // If team ID was explicitly set (e.g., by middleware), use it
        if ($this->teamId !== null) {
            return $this->teamId;
        }

        // Otherwise, resolve from authenticated user's current_team_id
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->current_team_id;
    }

    /**
     * Set the team ID explicitly.
     * This allows middleware or other code to override the automatic resolution.
     *
     * @param  int|string|Model|null  $id
     */
    public function setPermissionsTeamId($id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->teamId = $id;
    }
}
