<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Services\Concerns\TransformsResources;
use App\Http\Resources\Permissions\PermissionResource;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Resources\Permissions\PermissionCollection;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionService extends BaseService implements PermissionServiceInterface
{
    use TransformsResources;

    public function __construct(
        PermissionRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PermissionCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): PermissionCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): PermissionResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): PermissionResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Permission $model
     */
    public function update(Model $model, array $data): PermissionResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Permission $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource
    {
        $permission = $this->repository->find($permissionId);
        $permission->syncRoles($roleIds);

        return $this->toResource($permission->fresh(['roles']));
    }

    protected function getResourceClass(): string
    {
        return PermissionResource::class;
    }

    protected function getCollectionClass(): string
    {
        return PermissionCollection::class;
    }
}
