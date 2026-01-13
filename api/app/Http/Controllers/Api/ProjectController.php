<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Cache\CacheInvalidationService;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Projects\ProjectIndexRequest;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Services\Contracts\ProjectServiceInterface;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class ProjectController extends Controller
{
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

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
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $user->refresh();
        $teamId = $user->getAttributeValue('current_team_id');

        $perPage = (int) $validated['per_page'];
        $collection = $this->projectService->getPaginated($perPage, $teamId);

        return ApiResponse::success($collection);
    }

    /**
     * Get a specific project by ID.
     *
     * @authenticated
     */
    public function show(Project $project): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $projectResource = $this->projectService->findById($project->id, $teamId);

        return ApiResponse::success($projectResource);
    }

    /**
     * Create a new project.
     *
     * @authenticated
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $projectResource = $this->projectService->createProject($request->validated(), $teamId);

        return ApiResponse::created($projectResource);
    }

    /**
     * Update project information.
     *
     * @authenticated
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        // Check if project belongs to user's team (this will throw 404 if not found)
        $this->projectService->findById($project->id, $teamId);

        $validated = $request->validated();

        $projectResource = $this->projectService->updateProject($project, $validated, $teamId);

        // Invalidate project and team caches
        CacheInvalidationService::invalidateTeam($teamId ?? 0);

        return ApiResponse::success($projectResource);
    }

    /**
     * Delete a project.
     *
     * @authenticated
     */
    public function destroy(Project $project): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        // Check if project belongs to user's team
        $projectResource = $this->projectService->findById($project->id, $teamId);

        $this->projectService->deleteProject($projectResource->resource);

        // Invalidate project and team caches
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return ApiResponse::noContent('Project deleted successfully');
    }
}
