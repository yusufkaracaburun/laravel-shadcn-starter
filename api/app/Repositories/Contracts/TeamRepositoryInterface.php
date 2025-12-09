<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\QueryableRepositoryInterface;

interface TeamRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get all teams for a specific user.
     */
    public function getTeamsForUser(int $userId): Collection;

    /**
     * Get personal team for a user.
     */
    public function getPersonalTeamForUser(int $userId): ?Team;

    /**
     * Get teams owned by a user.
     */
    public function getOwnedTeamsForUser(int $userId): Collection;
}
