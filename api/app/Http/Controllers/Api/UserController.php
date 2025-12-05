<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Support\Cache\CacheInvalidationService;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class UserController extends Controller
{
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    public function __construct(
        private readonly UserRepository $userRepository
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

        $perPage = (int) ($validated['per_page'] ?? 15);
        $paginator = $this->userRepository->getPaginated($perPage, $teamId);

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

        $user = $this->userRepository->findById($user->id, $teamId);

        return ApiResponse::success(new UserResource($user));
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

        $user = $this->userRepository->create($request->validated(), $teamId);

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            $user->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        return ApiResponse::created(new UserResource($user));
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

        $user = $this->userRepository->update($user, $validated, $teamId);

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            // Clear existing profile photo (singleFile collection)
            $user->clearMediaCollection('profile-photos');
            // Add new profile photo
            $user->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

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
        $userId = $user->id;

        $this->userRepository->delete($user);

        // Invalidate user and team caches
        CacheInvalidationService::invalidateUser($userId);
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

        $user = $this->userRepository->getCurrentUser($user);

        return ApiResponse::success(new UserResource($user));
    }

    /**
     * Get available roles.
     *
     * @authenticated
     */
    public function roles(): JsonResponse
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]);

        return ApiResponse::success($roles->values()->all());
    }
}
