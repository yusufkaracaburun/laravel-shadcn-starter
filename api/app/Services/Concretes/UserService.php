<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserCollection;
use App\Services\Concerns\TransformsResources;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

 // Added this import

final class UserService extends BaseService implements UserServiceInterface
{
    use TransformsResources;

    public function __construct(
        UserRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): UserCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): UserCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): UserResource
    {
        $user = $this->repository->find($id);

        return $this->toResource($user->load(['teams', 'currentTeam', 'ownedTeams', 'roles']));
    }

    public function create(array $data): UserResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param User $model
     */
    public function update(Model $model, array $data): UserResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param User $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    public function getCurrentUser(User $user): UserResource
    {
        $response = $this->repository->getCurrentUser($user);

        return $this->toResource($response);
    }

    public function getVerifiedUsers(): AnonymousResourceCollection
    {
        $response = $this->repository->getVerifiedUsers();

        return UserResource::collection($response);
    }

    public function getActiveUsers(): AnonymousResourceCollection
    {
        $response = $this->repository->getActiveUsers();

        return UserResource::collection($response);
    }

    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): AnonymousResourceCollection
    {
        $response = $this->repository->getUsersByStatus($status);

        return UserResource::collection($response);
    }

    public function getAllFiltered(): AnonymousResourceCollection
    {
        return UserResource::collection($this->repository->getAllFiltered());
    }

    public function getVerifiedFiltered(): AnonymousResourceCollection
    {
        return UserResource::collection($this->repository->getVerifiedFiltered());
    }

    protected function getResourceClass(): string
    {
        return UserResource::class;
    }

    protected function getCollectionClass(): string
    {
        return UserCollection::class;
    }
}
