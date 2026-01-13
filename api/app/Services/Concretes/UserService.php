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

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->setRepository($repository);
        $this->userRepository = $repository;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): UserCollection
    {
        return new UserCollection(
            $this->userRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): UserCollection
    {
        return new UserCollection(
            $this->userRepository->all($columns),
        );
    }

    public function findById(int $id): UserResource
    {
        $user = $this->userRepository->find($id);

        return new UserResource($user->load(['teams', 'currentTeam', 'ownedTeams', 'roles']));
    }

    public function createUser(array $data): UserResource
    {
        return new UserResource(
            parent::create($data),
        );
    }

    public function updateUser(User $user, array $data): UserResource
    {
        return new UserResource(
            parent::update($user, $data),
        );
    }

    public function deleteUser(User $user): bool
    {
        return parent::delete($user);
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
