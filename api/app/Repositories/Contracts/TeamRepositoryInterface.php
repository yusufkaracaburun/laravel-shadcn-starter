<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface TeamRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get paginated teams with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     * Teams are scoped to the authenticated user (teams they own or belong to).
     *
     * @return LengthAwarePaginator<Team>
     */
    public function getPaginated(int $perPage, ?int $userId = null): LengthAwarePaginator;

    /**
     * Find a team by ID with user access check.
     * Only returns team if user owns it or belongs to it.
     */
    public function findById(int $teamId, ?int $userId = null): Team;

    /**
     * Create a new team for a user.
     *
     * @param  array<string, mixed>  $data
     * @param  int  $userId  User ID for team creation
     */
    public function createTeam(array $data, int $userId): Team;

    /**
     * Update a team by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeam(Team $team, array $data): Team;

    /**
     * Delete a team by model instance.
     */
    public function deleteTeam(Team $team): bool;

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
