<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\TeamIndexRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Helpers\Cache\CacheInvalidationService;
use App\Services\Contracts\TeamServiceInterface;

final class TeamController extends BaseApiController
{
    public function __construct(
        private readonly TeamServiceInterface $service,
    ) {}

    /**
     * Display a paginated list of teams.
     *
     * @authenticated
     */
    public function index(TeamIndexRequest $request): JsonResponse
    {
        return $this->respondWithCollection(
            $this->service->getPaginatedByRequest($request),
        );
    }

    /**
     * Get a specific team by ID.
     *
     * @authenticated
     */
    public function show(Team $team): JsonResponse
    {
        $currentUser = $this->getAuthenticatedUser();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if ($isSuperAdmin) {
            // Super admin can access any team
            $teamResource = $this->service->findById($team->id);
        } else {
            // For regular users, check authorization first
            $this->authorize('view', $team);
            $teamResource = $this->service->findById($team->id);
        }

        return $this->respondWithResource($teamResource);
    }

    /**
     * Create a new team.
     *
     * @authenticated
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $currentUser = $this->getAuthenticatedUser();

        $teamResource = $this->service->create($request->validated());

        // Add user to team as owner (if not already attached)
        $team = $teamResource->resource;
        if (!$currentUser->teams()->where('teams.id', $team->id)->exists()) {
            $currentUser->teams()->attach($team->id, ['role' => 'owner']);
        }

        // Set as current team if user has no current team
        if (!$currentUser->current_team_id) {
            $currentUser->update(['current_team_id' => $team->id]);
        }

        // Invalidate caches
        CacheInvalidationService::invalidateUser($currentUser->id);

        return $this->respondCreated($teamResource);
    }

    /**
     * Update team information.
     *
     * @authenticated
     */
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $currentUser = $this->getAuthenticatedUser();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if (!$isSuperAdmin) {
            // For regular users, check authorization first
            $this->authorize('update', $team);
        }

        $teamResource = $this->service->update($team, $request->validated());

        // Invalidate team and user caches
        CacheInvalidationService::invalidateTeam($team->id);
        CacheInvalidationService::invalidateUser($currentUser->id);

        return $this->respondWithResource($teamResource);
    }

    /**
     * Delete a team.
     *
     * @authenticated
     */
    public function destroy(Team $team): JsonResponse
    {
        $currentUser = $this->getAuthenticatedUser();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if (!$isSuperAdmin) {
            // For regular users, check authorization first
            $this->authorize('delete', $team);
        }

        // If this was the current team for any user, clear it
        if ($currentUser->current_team_id === $team->id) {
            $currentUser->update(['current_team_id' => null]);
        }

        $teamId = $team->id;
        $this->service->delete($team);

        // Invalidate caches
        CacheInvalidationService::invalidateTeam($teamId);
        CacheInvalidationService::invalidateUser($currentUser->id);

        return $this->respondNoContent('Team deleted successfully');
    }

    /**
     * Switch the user's current team.
     *
     * @authenticated
     */
    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $user = $this->getAuthenticatedUser();
        $oldTeamId = $user->current_team_id;

        try {
            $user->switchTeam($validated['team_id']);
        } catch (InvalidArgumentException $invalidArgumentException) {
            return ApiResponse::error($invalidArgumentException->getMessage(), Response::HTTP_FORBIDDEN);
        }

        // Invalidate caches for both old and new team
        if ($oldTeamId !== null) {
            CacheInvalidationService::onTeamSwitched($user->id, $oldTeamId, $validated['team_id']);
        } else {
            CacheInvalidationService::invalidateUser($user->id);
            CacheInvalidationService::invalidateTeam($validated['team_id']);
        }

        $user->refresh();
        $user->load('currentTeam', 'teams');
        $user->makeVisible(['current_team_id']);

        return $this->respondWithResource([
            'user'         => $user,
            'current_team' => $user->currentTeam,
        ]);
    }

    public function prerequisites(): JsonResponse
    {
        return $this->respondWithPrerequisites([]);
    }
}
