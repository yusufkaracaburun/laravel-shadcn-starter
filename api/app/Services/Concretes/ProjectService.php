<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\Projects\ProjectCollection;
use App\Services\Contracts\ProjectServiceInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectService extends BaseService implements ProjectServiceInterface
{
    private readonly ProjectRepositoryInterface $projectRepository;

    public function __construct(
        ProjectRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
        $this->projectRepository = $repository;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProjectCollection
    {
        return new ProjectCollection(
            $this->projectRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): ProjectCollection
    {
        return new ProjectCollection(
            $this->projectRepository->all($columns),
        );
    }

    public function findById(int $id): ProjectResource
    {
        return new ProjectResource(
            $this->projectRepository->find($id),
        );
    }

    public function createProject(array $data): ProjectResource
    {
        return new ProjectResource(
            parent::create($data),
        );
    }

    public function updateProject(Project $project, array $data): ProjectResource
    {
        return new ProjectResource(
            parent::update($project, $data),
        );
    }

    public function deleteProject(Project $project): bool
    {
        return parent::delete($project);
    }
}
