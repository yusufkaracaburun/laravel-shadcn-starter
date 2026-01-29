<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Helpers\Cache\CacheInvalidationService;
use App\Services\Contracts\UserServiceInterface;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class UserController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    public function __construct(
        private readonly UserServiceInterface $service,
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * Get prerequisites for creating a new vehicle.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {        
        return ApiResponse::success([
            'roles' => Role::all(),
            'statuses' => UserStatus::toArray(),
        ]);
    }

    /**
     * Display a paginated list of users.
     *
     * @authenticated
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', User::class); // Uncomment when Policy is created

        $cache = User::getCacheKeys();
        $collection = $this->cachedResponse($cache['index'], fn () => $this->service->getPaginated($request));

        return ApiResponse::success($collection);
    }

    /**
     * Create a new user.
     *
     * @authenticated
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        // $this->authorize('create', User::class);

        $userResource = $this->service->createUser($request->validated());

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            $userResource->resource->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        return ApiResponse::created($userResource);
    }

    /**
     * Display the specified user.
     *
     * @authenticated
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $userResource = $this->service->show($user);

        return ApiResponse::success($userResource);
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

        $userResource = $this->service->updateUser($user, $validated, $teamId);

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

        $this->service->deleteUser($user);

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($userId);
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return ApiResponse::noContent('User deleted successfully');
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

        $userResource = $this->service->getCurrentUser($user);

        return ApiResponse::success($userResource);
    }

    /**
     * Get all users (non-paginated).
     *
     * @authenticated
     */
    public function all(): JsonResponse
    {
        $users = $this->service->getAll();

        return ApiResponse::success($users);
    }

    /**
     * Get active users.
     *
     * @authenticated
     */
    public function active(): JsonResponse
    {
        $users = $this->service->getActiveUsers();

        return ApiResponse::success($users);
    }

    /**
     * Get available roles with QueryBuilder support.
     *
     * Supports filtering and sorting via request parameters.
     * Example: /api/users/roles?filter[name]=admin&sort=name
     *
     * @authenticated
     */
    public function roles(Request $request): JsonResponse
    {
        $this->roleRepository->withRequest($request);

        $roles = $this->roleRepository->query()
            ->where('guard_name', 'web')
            ->get(['id', 'name'])
            ->map(fn ($role): array => [
                'id'   => $role->id,
                'name' => $role->name,
            ]);

        return ApiResponse::success($roles->values()->all());
    }
}
