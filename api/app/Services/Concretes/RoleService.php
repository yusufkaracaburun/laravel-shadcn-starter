<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use InvalidArgumentException;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Roles\RoleCollection;
use App\Services\Concerns\TransformsResources;
use App\Services\Contracts\RoleServiceInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;

final class RoleService extends BaseService implements RoleServiceInterface
{
    use TransformsResources;

    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): RoleCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): RoleCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): RoleResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function findByName(string $name): ?Role
    {
        return $this->repository->findByName($name);
    }

    public function create(array $data): RoleResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Role $model
     */
    public function update(Model $model, array $data): RoleResource
    {
        // Prevent updating system roles
        throw_if($model->is_system, InvalidArgumentException::class, 'Cannot update system roles');

        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Role $model
     */
    public function delete(Model $model): bool
    {
        // Prevent deleting system roles
        throw_if($model->is_system, InvalidArgumentException::class, 'Cannot delete system roles');

        return $this->repository->delete($model->id);
    }

    public function assignPermissions(int $roleId, array $permissionIds): RoleResource
    {
        $role = $this->repository->find($roleId);
        $role->syncPermissions($permissionIds);

        return $this->toResource($role->fresh(['permissions']));
    }

    public function getNonSystemRoles(): RoleCollection
    {
        $roles = $this->repository->getSystemRoles(false);

        return $this->toCollection($roles);
    }

    public function getWebRolesFiltered(): array
    {
        return $this->repository->getWebRolesFiltered()
            ->map(fn ($role): array => [
                'id'   => $role->id,
                'name' => $role->name,
            ])
            ->values()
            ->all();
    }

    protected function getResourceClass(): string
    {
        return RoleResource::class;
    }

    protected function getCollectionClass(): string
    {
        return RoleCollection::class;
    }
}
