<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\User;
use App\Enums\UserStatus;
use App\Services\BaseService;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserService extends BaseService implements UserServiceInterface
{
    private readonly UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $repository
    ) {
        $this->setRepository($repository);
        $this->userRepository = $repository;
    }

    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): UserCollection
    {
        $paginated = $this->userRepository->getPaginated($perPage, $teamId);

        return new UserCollection($paginated);
    }

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): UserResource
    {
        $user = $this->userRepository->findById($userId, $teamId);

        return new UserResource($user);
    }

    /**
     * Update a user by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function updateUser(User $user, array $data, ?int $teamId = null): UserResource
    {
        $user = $this->userRepository->updateUser($user, $data, $teamId);

        return new UserResource($user);
    }

    /**
     * Delete a user by model instance.
     */
    public function deleteUser(User $user): bool
    {
        return $this->userRepository->deleteUser($user);
    }

    /**
     * Create a new user with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function createUser(array $data, ?int $teamId = null): UserResource
    {
        $user = $this->userRepository->createUser($data, $teamId);

        return new UserResource($user);
    }

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): UserResource
    {
        $user = $this->userRepository->getCurrentUser($user);

        return new UserResource($user);
    }

    /**
     * Get all verified users.
     *
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getVerifiedUsers(): AnonymousResourceCollection
    {
        $users = $this->userRepository->getVerifiedUsers();

        return UserResource::collection($users);
    }

    /**
     * Get all active users.
     *
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getActiveUsers(): AnonymousResourceCollection
    {
        $users = $this->userRepository->getActiveUsers();

        return UserResource::collection($users);
    }

    /**
     * Get users by status.
     *
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): AnonymousResourceCollection
    {
        $users = $this->userRepository->getUsersByStatus($status);

        return UserResource::collection($users);
    }
}
