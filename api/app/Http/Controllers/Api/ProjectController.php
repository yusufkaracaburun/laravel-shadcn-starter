<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Helpers\Cache\CacheInvalidationService;
use App\Http\Requests\Projects\ProjectIndexRequest;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Services\Contracts\ProjectServiceInterface;
use App\Http\Requests\Projects\UpdateProjectRequest;

final class ProjectController extends BaseApiController
{
    public function __construct(
        private readonly ProjectServiceInterface $projectService,
    ) {}

    /**
     * Display a paginated list of projects.
     *
     * @authenticated
     */
    public function index(ProjectIndexRequest $request): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        return $this->respondWithCollection(
            $this->projectService->getPaginatedByRequest($request),
        );
    }

    /**
     * Get a specific project by ID.
     *
     * @authenticated
     */
    public function show(Project $project): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        return $this->respondWithResource(
            $this->projectService->findById($project->id),
        );
    }

    /**
     * Create a new project.
     *
     * @authenticated
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        return $this->respondCreated(
            $this->projectService->create($request->validated()),
        );
    }

    /**
     * Update project information.
     *
     * @authenticated
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        // Check if project belongs to user's team (this will throw 404 if not found)
        $this->projectService->findById($project->id);

        $projectResource = $this->projectService->update($project, $request->validated());

        // Invalidate project and team caches
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return $this->respondWithResource($projectResource);
    }

    /**
     * Delete a project.
     *
     * @authenticated
     */
    public function destroy(Project $project): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        // Check if project belongs to user's team
        $projectResource = $this->projectService->findById($project->id);

        $this->projectService->delete($projectResource->resource);

        // Invalidate project and team caches
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return $this->respondNoContent('Project deleted successfully');
    }
}
