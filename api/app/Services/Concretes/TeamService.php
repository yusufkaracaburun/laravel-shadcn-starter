<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Team;
use Illuminate\Http\Request;
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

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): TeamCollection
    {
        $response = $this->teamRepository->paginateFiltered($request, $columns);

        return new TeamCollection($response);
    }

    public function getAll(array $columns = ['*']): TeamCollection
    {
        $response = $this->teamRepository->all($columns);

        return new TeamCollection($response);
    }

    public function findById(int $id): TeamResource
    {
        $response = $this->teamRepository->find($id);

        return new TeamResource($response);
    }

    public function createTeam(array $data): TeamResource
    {
        $response = $this->teamRepository->create($data);

        return new TeamResource($response);
    }

    public function updateTeam(Team $team, array $data): TeamResource
    {
        $response = $this->teamRepository->update($team->id, $data);

        return new TeamResource($response);
    }

    public function deleteTeam(Team $team): bool
    {
        return $this->teamRepository->delete($team->id);
    }
}
