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

final class TeamController extends Controller
{
    /**
     * Display a listing of the user's teams.
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Get all teams the user belongs to (both owned and member)
        $ownedTeams = $user->ownedTeams()->get();
        $memberTeams = $user->teams()->get();

        $teams = $ownedTeams->merge($memberTeams)->unique('id')->values();

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

        $team = Team::create([
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

        return ApiResponse::success($team);
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

        $team->delete();

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

        try {
            $user->switchTeam($validated['team_id']);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
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
