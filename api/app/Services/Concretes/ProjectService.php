<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Project;
use App\Services\BaseService;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectCollection;
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

    /**
     * Get paginated projects with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): ProjectCollection
    {
        $paginated = $this->projectRepository->getPaginated($perPage, $teamId);

        return new ProjectCollection($paginated);
    }

    /**
     * Find a project by ID with relationships loaded.
     */
    public function findById(int $projectId, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->findById($projectId, $teamId);

        return new ProjectResource($project);
    }

    /**
     * Create a new project with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped project creation
     */
    public function createProject(array $data, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->createProject($data, $teamId);

        return new ProjectResource($project);
    }

    /**
     * Update a project by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateProject(Project $project, array $data, ?int $teamId = null): ProjectResource
    {
        $project = $this->projectRepository->updateProject($project, $data, $teamId);

        return new ProjectResource($project);
    }

    /**
     * Delete a project by model instance.
     */
    public function deleteProject(Project $project): bool
    {
        return $this->projectRepository->deleteProject($project);
    }
}
