<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Team;
use App\Services\BaseService;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Teams\TeamCollection;
use App\Services\Contracts\TeamServiceInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamService extends BaseService implements TeamServiceInterface
{
    private readonly TeamRepositoryInterface $teamRepository;

    public function __construct(
        TeamRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
        $this->teamRepository = $repository;
    }

    /**
     * Get paginated teams with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     * Teams are scoped to the authenticated user (teams they own or belong to).
     */
    public function getPaginated(int $perPage, ?int $userId = null): TeamCollection
    {
        $paginated = $this->teamRepository->getPaginated($perPage, $userId);

        return new TeamCollection($paginated);
    }

    /**
     * Find a team by ID with user access check.
     */
    public function findById(int $teamId, ?int $userId = null): TeamResource
    {
        $team = $this->teamRepository->findById($teamId, $userId);

        return new TeamResource($team);
    }

    /**
     * Create a new team for a user.
     *
     * @param  array<string, mixed>  $data
     * @param  int  $userId  User ID for team creation
     */
    public function createTeam(array $data, int $userId): TeamResource
    {
        $team = $this->teamRepository->createTeam($data, $userId);

        return new TeamResource($team);
    }

    /**
     * Update a team by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeam(Team $team, array $data): TeamResource
    {
        $team = $this->teamRepository->updateTeam($team, $data);

        return new TeamResource($team);
    }

    /**
     * Delete a team by model instance.
     */
    public function deleteTeam(Team $team): bool
    {
        return $this->teamRepository->deleteTeam($team);
    }
}
