<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserCollection;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UserService extends BaseService implements UserServiceInterface
{
    private readonly UserRepositoryInterface $repo;

    public function __construct(
        UserRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(Request $request): UserCollection
    {
        $paginated = $this->repo->paginateFiltered($request);

        return new UserCollection($paginated);
    }

    public function show(User $user): UserResource
    {
        $user = $this->repo->findForShow($user);

        return new UserResource($user);
    }

    public function createUser(array $data): UserResource
    {
        $user = $this->repo->createWithRelationships($data);

        if (request()->hasFile('profile_photo')) {
            $user->resource->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile-photos');
        }

        return new UserResource($user);
    }

    public function updateUser(User $user, array $data): UserResource
    {
        $updated = $this->repo->updateWithRelationships($user, $data);

        return new UserResource($updated);
    }

    public function deleteUser(User $user): bool
    {
        return $this->repo->delete($user);
    }

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId): UserResource
    {
        $user = $this->repo->findOrFail($userId);

        return new UserResource($user);
    }

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): UserResource
    {
        $user = $this->repo->getCurrentUser($user);

        return new UserResource($user);
    }

    /**
     * Get all users.
     */
    public function getAll(): UserCollection
    {
        $users = $this->repo->all();

        return new UserCollection($users);
    }

    /**
     * Get all verified users.
     */
    public function getVerifiedUsers(): UserCollection
    {
        $users = $this->repo->getVerifiedUsers();

        return new UserCollection($users);
    }

    /**
     * Get all active users.
     */
    public function getActiveUsers(): UserCollection
    {
        $users = $this->repo->getActiveUsers();

        return new UserCollection($users);
    }

    /**
     * Get users by status.
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): UserCollection
    {
        $users = $this->repo->getUsersByStatus($status);

        return new UserCollection($users);
    }
}
