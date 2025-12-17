<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\TeamIndexRequest;
use App\Http\Requests\UpdateTeamRequest;
use Spatie\Permission\PermissionRegistrar;
use App\Helpers\Cache\CacheInvalidationService;
use App\Services\Contracts\TeamServiceInterface;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class TeamController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly TeamServiceInterface $teamService,
    ) {}

    /**
     * Display a paginated list of teams.
     *
     * @authenticated
     */
    public function index(TeamIndexRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $user->refresh();

        $perPage = (int) $validated['per_page'];
        $collection = $this->teamService->getPaginated($perPage, $user->id);

        return ApiResponse::success($collection);
    }

    /**
     * Get a specific team by ID.
     *
     * @authenticated
     */
    public function show(Team $team): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $currentUser->refresh();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if ($isSuperAdmin) {
            // Super admin can access any team - pass null to skip user filtering
            $teamResource = $this->teamService->findById($team->id);
        } else {
            // For regular users, findById will throw 404 if team doesn't belong to user
            // This matches the expected behavior where teams not belonging to user return 404
            $teamResource = $this->teamService->findById($team->id, $currentUser->id);
            // After finding, check if user has permission to view (for teams they belong to)
            $this->authorize('view', $team);
        }

        return ApiResponse::success($teamResource);
    }

    /**
     * Create a new team.
     *
     * @authenticated
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamResource = $this->teamService->createTeam($request->validated(), $currentUser->id);

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

        return ApiResponse::created($teamResource);
    }

    /**
     * Update team information.
     *
     * @authenticated
     */
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if ($isSuperAdmin) {
            // Super admin can update any team - skip authorization and user filtering
        } else {
            // For regular users, check authorization first
            $this->authorize('update', $team);
            // Then verify team belongs to user (this will throw 404 if not found)
            $this->teamService->findById($team->id, $currentUser->id);
        }

        $validated = $request->validated();

        $teamResource = $this->teamService->updateTeam($team, $validated);

        // Invalidate team and user caches
        CacheInvalidationService::invalidateTeam($team->id);
        CacheInvalidationService::invalidateUser($currentUser->id);

        return ApiResponse::success($teamResource);
    }

    /**
     * Delete a team.
     *
     * @authenticated
     */
    public function destroy(Team $team): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        // Check if user is super admin
        $isSuperAdmin = $this->isSuperAdmin($currentUser);

        if ($isSuperAdmin) {
            // Super admin can delete any team - skip authorization and user filtering
        } else {
            // For regular users, check authorization first
            $this->authorize('delete', $team);
            // Then verify team belongs to user (this will throw 404 if not found)
            $this->teamService->findById($team->id, $currentUser->id);
        }

        // If this was the current team for any user, clear it
        if ($currentUser->current_team_id === $team->id) {
            $currentUser->update(['current_team_id' => null]);
        }

        $teamId = $team->id;
        $this->teamService->deleteTeam($team);

        // Invalidate caches
        CacheInvalidationService::invalidateTeam($teamId);
        CacheInvalidationService::invalidateUser($currentUser->id);

        return ApiResponse::noContent('Team deleted successfully');
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

        /** @var User $user */
        $user = Auth::user();
        $user->refresh();

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

        return ApiResponse::success([
            'user'         => $user,
            'current_team' => $user->currentTeam,
        ]);
    }

    /**
     * Check if user is a super admin.
     */
    private function isSuperAdmin(User $user): bool
    {
        // Use the exact same logic as BasePolicy::before() to ensure consistency
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

        // Clear permission cache and roles relation to ensure fresh check
        $permissionRegistrar->forgetCachedPermissions();

        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $user->unsetRelation('roles');
        $isSuperAdmin = $user->hasRole('super-admin');

        // Restore original team context
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);

        return $isSuperAdmin;
    }
}
