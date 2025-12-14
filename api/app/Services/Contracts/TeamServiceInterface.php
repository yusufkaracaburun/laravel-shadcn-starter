<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Http\Resources\TeamCollection;
use App\Services\BaseServiceInterface;

interface TeamServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated teams with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     * Teams are scoped to the authenticated user (teams they own or belong to).
     */
    public function getPaginated(int $perPage, ?int $userId = null): TeamCollection;

    /**
     * Find a team by ID with user access check.
     */
    public function findById(int $teamId, ?int $userId = null): TeamResource;

    /**
     * Create a new team for a user.
     *
     * @param  array<string, mixed>  $data
     * @param  int  $userId  User ID for team creation
     */
    public function createTeam(array $data, int $userId): TeamResource;

    /**
     * Update a team by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeam(Team $team, array $data): TeamResource;

    /**
     * Delete a team by model instance.
     */
    public function deleteTeam(Team $team): bool;
}
