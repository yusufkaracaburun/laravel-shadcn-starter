<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Project;
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

    public function getPaginated(int $perPage, ?int $teamId = null): ProjectCollection
    {
        $paginated = $this->projectRepository->getPaginated($perPage, $teamId);

        return new ProjectCollection($paginated);
    }

    public function findById(int $projectId, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->findById($projectId, $teamId);

        return new ProjectResource($project);
    }

    public function createProject(array $data, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->createProject($data, $teamId);

        return new ProjectResource($project);
    }

    public function updateProject(Project $project, array $data, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->updateProject($project, $data, $teamId);

        return new ProjectResource($project);
    }

    public function deleteProject(Project $project): bool
    {
        return $this->projectRepository->deleteProject($project);
    }
}
