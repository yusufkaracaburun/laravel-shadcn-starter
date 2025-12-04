<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\Cache\TeamCache;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserIndexRequest;
use App\Support\Cache\CacheInvalidationService;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class UserController extends Controller
{
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    /**
     * Display a paginated list of users.
     *
     * @param UserIndexRequest $request
     * @authenticated
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $user->refresh();
        $teamId = $user->getAttributeValue('current_team_id');

        if ($teamId === null) {
            // If no team is set, return all users with pagination
            $paginator = User::query()->paginate($validated['per_page']);
        } else {
            // Team-scoped users with pagination
            $paginator = User::query()
                ->whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))
                ->orWhereHas('ownedTeams', fn ($query) => $query->where('id', $teamId))
                ->paginate($validated['per_page']);
        }

        // Transform items using UserResource
        $paginator->setCollection(
            UserResource::collection($paginator->items())->collection
        );

        // Get Laravel's pagination JSON structure
        $paginationData = $paginator->toArray();

        return ApiResponse::success($paginationData);
    }

    /**
     * Get a specific user by ID.
     *
     * @authenticated
     */
    public function show(User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $currentUser->refresh();
        $teamId = $currentUser->getAttributeValue('current_team_id');

        if ($teamId === null) {
            $user->load(['teams', 'currentTeam', 'ownedTeams']);

            return ApiResponse::success(new UserResource($user));
        }

        $cachedUser = TeamCache::remember(
            $teamId,
            "users:{$user->id}",
            fn () => $user->load(['teams', 'currentTeam', 'ownedTeams'])
        );

        return ApiResponse::success(new UserResource($cachedUser));
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

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        return ApiResponse::created(new UserResource($user));
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
        $user->refresh();
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($user->id);
        if ($user->current_team_id) {
            CacheInvalidationService::invalidateTeam($user->current_team_id);
        }

        return ApiResponse::success(new UserResource($user));
    }

    /**
     * Delete a user.
     *
     * @authenticated
     */
    public function destroy(User $user): Response
    {
        $teamId = $user->current_team_id;
        $user->delete();

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($user->id);
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return ApiResponse::noContent();
    }

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

        return ApiResponse::success(new UserResource($user));
    }
}
