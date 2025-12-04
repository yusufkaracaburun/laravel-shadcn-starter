<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserIndexRequest;
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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['sometimes', 'image', 'max:2048'], // Max 2MB
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        // Handle profile photo upload if present
        if ($request->hasFile('profile_photo')) {
            $userData['profile_photo'] = $request->file('profile_photo');
        }

        $user = $this->userRepository->create($userData);

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

        $user = $this->userRepository->update($user, $validated);

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
}
