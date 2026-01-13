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

    public function __construct(TeamRepositoryInterface $repository)
    {
        $this->setRepository($repository);
        $this->teamRepository = $repository;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): TeamCollection
    {
        return new TeamCollection(
            $this->teamRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): TeamCollection
    {
        return new TeamCollection(
            $this->teamRepository->all($columns),
        );
    }

    public function findById(int $id): TeamResource
    {
        return new TeamResource(
            $this->teamRepository->find($id),
        );
    }

    public function createTeam(array $data): TeamResource
    {
        return new TeamResource(
            parent::create($data),
        );
    }

    public function updateTeam(Team $team, array $data): TeamResource
    {
        return new TeamResource(
            parent::update($team, $data),
        );
    }

    public function deleteTeam(Team $team): bool
    {
        return parent::delete($team);
    }
}
