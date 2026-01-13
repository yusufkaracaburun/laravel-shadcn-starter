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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserService extends BaseService implements UserServiceInterface
{
    private readonly UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
        $this->userRepository = $repository;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): UserCollection
    {
        $response = $this->userRepository->paginateFiltered($request, $columns);

        return new UserCollection($response);
    }

    public function getAll(array $columns = ['*']): UserCollection
    {
        $response = $this->userRepository->all($columns);

        return new UserCollection($response);
    }

    public function findById(int $id): UserResource
    {
        $response = $this->userRepository->find($id);

        return new UserResource($response);
    }

    public function createUser(array $data): UserResource
    {
        $response = $this->userRepository->create($data);

        return new UserResource($response);
    }

    public function updateUser(User $user, array $data): UserResource
    {
        $response = $this->userRepository->update($user->id, $data);

        return new UserResource($response);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user->id);
    }

    public function getCurrentUser(User $user): UserResource
    {
        $response = $this->userRepository->getCurrentUser($user);

        return new UserResource($response);
    }

    public function getVerifiedUsers(): AnonymousResourceCollection
    {
        $response = $this->userRepository->getVerifiedUsers();

        return UserResource::collection($response);
    }

    public function getActiveUsers(): AnonymousResourceCollection
    {
        $response = $this->userRepository->getActiveUsers();

        return UserResource::collection($response);
    }

    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): AnonymousResourceCollection
    {
        $response = $this->userRepository->getUsersByStatus($status);

        return UserResource::collection($response);
    }
}
