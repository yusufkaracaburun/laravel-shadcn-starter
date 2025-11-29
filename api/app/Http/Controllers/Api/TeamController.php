<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use App\Support\Cache\TeamCache;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Support\Cache\CacheInvalidationService;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class TeamController extends Controller
{
    use InvalidatesCachedModels;

    use UsesCachedResponses;

    /**
     * Display a listing of the user's teams.
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $teams = $this->cachedResponse(
            'api.teams.index',
            function () use ($user) {
                $ownedTeams = $user->ownedTeams()->get();
                $memberTeams = $user->teams()->get();

                return $ownedTeams->merge($memberTeams)->unique('id')->values()->toArray();
            }
        );

        return ApiResponse::success($teams);
    }

    /**
     * Store a newly created team.
     *
     * @authenticated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'personal_team' => ['sometimes', 'boolean'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->refresh(); // Ensure all attributes are loaded

        $team = \App\Models\Team::query()->create([
            'name' => $validated['name'],
            'user_id' => $user->id,
            'personal_team' => $validated['personal_team'] ?? false,
        ]);

        // Add user to team as owner
        $user->teams()->attach($team->id, ['role' => 'owner']);

        // Set as current team if user has no current team
        if (! $user->current_team_id) {
            $user->update(['current_team_id' => $team->id]);
        }

        // Invalidate caches
        $this->invalidateAfterCreate('team', $team->id);
        CacheInvalidationService::invalidateUser($user->id);

        return ApiResponse::created($team);
    }

    /**
     * Display the specified team.
     *
     * @authenticated
     */
    public function show(Team $team): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if user belongs to this team
        if (! $user->teams()->where('teams.id', $team->id)->exists() &&
            ! $user->ownedTeams()->where('id', $team->id)->exists()) {
            return ApiResponse::error('Unauthorized', Response::HTTP_FORBIDDEN);
        }

        $cachedTeam = TeamCache::remember(
            $team->id,
            "team:{$team->id}",
            fn () => $team->load(['users', 'owner'])
        );

        return ApiResponse::success($cachedTeam);
    }

    /**
     * Update the specified team.
     *
     * @authenticated
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Only team owner can update
        if ($team->user_id !== $user->id) {
            return ApiResponse::error('Only team owner can update the team', Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'personal_team' => ['sometimes', 'boolean'],
        ]);

        $team->update($validated);

        // Invalidate caches
        $this->invalidateAfterUpdate('team', $team->id);

        return ApiResponse::success($team);
    }

    /**
     * Remove the specified team.
     *
     * @authenticated
     */
    public function destroy(Team $team): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $user->refresh(); // Ensure all attributes are loaded

        // Only team owner can delete
        if ($team->user_id !== $user->id) {
            return ApiResponse::error('Only team owner can delete the team', Response::HTTP_FORBIDDEN);
        }

        // If this was the current team, clear it
        if ($user->current_team_id === $team->id) {
            $user->update(['current_team_id' => null]);
        }

        $teamId = $team->id;
        $team->delete();

        // Invalidate caches
        $this->invalidateAfterDelete('team', $teamId);
        CacheInvalidationService::invalidateUser($user->id);

        return ApiResponse::noContent();
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
            'user' => $user,
            'current_team' => $user->currentTeam,
        ]);
    }
}
