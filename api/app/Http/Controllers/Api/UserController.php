<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Helpers\Cache\CacheInvalidationService;
use App\Services\Contracts\RoleServiceInterface;
use App\Services\Contracts\UserServiceInterface;

final class UserController extends BaseApiController
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly RoleServiceInterface $roleService,
    ) {}

    /**
     * Display a paginated list of users.
     *
     * @authenticated
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        return $this->respondWithCollection(
            $this->userService->getPaginatedByRequest($request),
        );
    }

    /**
     * Get a specific user by ID.
     *
     * @authenticated
     */
    public function show(User $user): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        return $this->respondWithResource(
            $this->userService->findById($user->id),
        );
    }

    /**
     * Create a new user.
     *
     * @authenticated
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        $userResource = $this->userService->create($request->validated());

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            $userResource->resource->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        return $this->respondCreated($userResource);
    }

    /**
     * Update user profile information.
     *
     * @authenticated
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $teamId = $this->getCurrentTeamId();

        $validated = $request->validated();

        // Remove profile_photo from validated data before updating user
        // Media Library handles file uploads separately
        unset($validated['profile_photo']);

        $userResource = $this->userService->update($user, $validated);

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            // Clear existing profile photo (singleFile collection)
            $userResource->resource->clearMediaCollection('profile-photos');
            // Add new profile photo
            $userResource->resource->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($userResource->resource->id);
        if ($userResource->resource->current_team_id) {
            CacheInvalidationService::invalidateTeam($userResource->resource->current_team_id);
        }

        return $this->respondWithResource($userResource);
    }

    /**
     * Delete a user.
     *
     * @authenticated
     */
    public function destroy(User $user): JsonResponse
    {
        $teamId = $user->current_team_id;
        $userId = $user->id;

        $this->userService->delete($user);

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($userId);
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return $this->respondNoContent('User deleted successfully');
    }

    /**
     * Get prerequisites for creating a new user.
     * Returns all roles and statuses.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        $statuses = UserStatus::toArray();

        return $this->respondWithPrerequisites(compact('roles', 'statuses'));
    }

    /**
     * Get the current authenticated user.
     *
     * @authenticated
     */
    public function current(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        return $this->respondWithResource(
            $this->userService->getCurrentUser($user),
        );
    }

    /**
     * Get all users (non-paginated).
     *
     * @authenticated
     */
    public function all(): JsonResponse
    {
        return $this->respondWithCollection(
            $this->userService->getAllFiltered(),
        );
    }

    /**
     * Get active users.
     *
     * @authenticated
     */
    public function active(): JsonResponse
    {
        return $this->respondWithCollection(
            $this->userService->getVerifiedFiltered(),
        );
    }

    /**
     * Get available roles with QueryBuilder support.
     *
     * Supports filtering and sorting via request parameters.
     * Example: /api/users/roles?filter[name]=admin&sort=name
     *
     * @authenticated
     */
    public function roles(): JsonResponse
    {
        return $this->respondWithCollection(
            $this->roleService->getWebRolesFiltered(),
        );
    }
}
