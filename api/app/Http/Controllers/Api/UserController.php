<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

final class UserController extends Controller
{
    /**
     * Get the current authenticated user.
     *
     * @authenticated
     */
    public function current(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Ensure current_team_id is loaded by refreshing the model
        $user->refresh();

        // Load teams and current team relationships
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        // Get all teams (owned + member) for response
        $allTeams = $user->ownedTeams->merge($user->teams)->unique('id')->values();

        // Build response data - use makeVisible to ensure current_team_id is included
        $user->makeVisible(['current_team_id']);
        $userData = $user->toArray();
        $userData['teams'] = $allTeams;
        $userData['currentTeam'] = $user->currentTeam;

        return ApiResponse::success($userData);
    }

    /**
     * Display a paginated list of users.
     *
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        $users = User::query()->get();

        return ApiResponse::success($users);
    }

    /**
     * Get a specific user by ID.
     *
     * @authenticated
     */
    public function show(User $user): JsonResponse
    {
        return ApiResponse::success($user);
    }

    /**
     * Create a new user.
     *
     * @authenticated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return ApiResponse::created($user);
    }

    /**
     * Update user profile information.
     *
     * @authenticated
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        $user->update($validated);

        return ApiResponse::success($user);
    }

    /**
     * Delete a user.
     *
     * @authenticated
     */
    public function destroy(User $user): Response
    {
        $user->delete();

        return ApiResponse::noContent();
    }
}
