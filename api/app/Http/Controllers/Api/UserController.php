<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Users\UserResource;
use App\Helpers\Cache\CacheInvalidationService;
use App\Services\Contracts\UserServiceInterface;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class UserController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    /**
     * Display a paginated list of users.
     *
     * @authenticated
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $user->refresh();
        $teamId = $user->getAttributeValue('current_team_id');

        $perPage = (int) $validated['per_page'];
        $collection = $this->userService->getPaginated($perPage, $teamId);

        return ApiResponse::success($collection);
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

        $userResource = $this->userService->findById($user->id, $teamId);

        return ApiResponse::success($userResource);
    }

    /**
     * Create a new user.
     *
     * @authenticated
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $userResource = $this->userService->createUser($request->validated(), $teamId);

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            $userResource->resource->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        return ApiResponse::created($userResource);
    }

    /**
     * Update user profile information.
     *
     * @authenticated
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $validated = $request->validated();

        // Remove profile_photo from validated data before updating user
        // Media Library handles file uploads separately
        unset($validated['profile_photo']);

        $userResource = $this->userService->updateUser($user, $validated, $teamId);

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

        return ApiResponse::success($userResource);
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

        $this->userService->deleteUser($user);

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($userId);
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return ApiResponse::noContent('User deleted successfully');
    }

    /**
     * Get prerequisites for creating a new invoice.
     * Returns all items, all customers, and the next invoice number.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        $statuses = UserStatus::toArray();

        return ApiResponse::ok(compact('roles', 'statuses'));
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

        $userResource = $this->userService->getCurrentUser($user);

        return ApiResponse::success($userResource);
    }

    /**
     * Get all users (non-paginated).
     *
     * @authenticated
     */
    public function all(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->refresh();

        $teamId = $user->getAttributeValue('current_team_id');

        $query = User::query();

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->whereHas('teams', fn ($q) => $q->where('teams.id', $teamId))
                ->orWhereHas('ownedTeams', fn ($q) => $q->where('id', $teamId));
        }

        $users = $this->buildQuery(
            $query,
            allowedFilters: ['id', 'name', 'email'],
            allowedSorts: ['id', 'name', 'email', 'created_at'],
            allowedIncludes: ['teams', 'currentTeam', 'ownedTeams', 'roles'],
        )->get();

        return ApiResponse::success(UserResource::collection($users));
    }

    /**
     * Get active users.
     *
     * @authenticated
     */
    public function active(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->refresh();

        $teamId = $user->getAttributeValue('current_team_id');

        $query = User::query()->whereNotNull('email_verified_at');

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->whereHas('teams', fn ($q) => $q->where('teams.id', $teamId))
                ->orWhereHas('ownedTeams', fn ($q) => $q->where('id', $teamId));
        }

        $users = $this->buildQuery(
            $query,
            allowedFilters: ['id', 'name', 'email'],
            allowedSorts: ['id', 'name', 'email', 'created_at'],
            allowedIncludes: ['teams', 'currentTeam', 'ownedTeams', 'roles'],
        )->get();

        return ApiResponse::success(UserResource::collection($users));
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
        $roles = $this->buildQuery(
            Role::query()->where('guard_name', 'web'),
            allowedFilters: ['name'],
            allowedSorts: ['id', 'name', 'created_at'],
        )
            ->get(['id', 'name'])
            ->map(fn ($role): array => [
                'id'   => $role->id,
                'name' => $role->name,
            ]);

        return ApiResponse::success($roles->values()->all());
    }
}
