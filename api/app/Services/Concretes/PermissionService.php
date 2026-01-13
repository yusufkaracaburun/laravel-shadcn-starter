<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Services\BaseService;
use App\Http\Resources\Permissions\PermissionResource;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Resources\Permissions\PermissionCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionService extends BaseService implements PermissionServiceInterface
{
    public function __construct(
        PermissionRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
    }

    public function getPermissions(): PermissionCollection
    {
        $permissions = $this->getFiltered();

        return new PermissionCollection($permissions);
    }

    public function getAll(): PermissionCollection
    {
        $permissions = $this->all();

        return new PermissionCollection($permissions);
    }

    public function getPaginated(int $perPage): PermissionCollection
    {
        $paginated = $this->paginate($perPage);

        return new PermissionCollection($paginated);
    }

    public function findById(int $permissionId): PermissionResource
    {
        try {
            $permission = $this->findOrFail($permissionId);

            return new PermissionResource($permission);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Permission not found');
        }
    }

    public function createPermission(array $data): PermissionResource
    {
        $permission = $this->create($data);

        return new PermissionResource($permission);
    }

    public function updatePermission(int $id, array $data): PermissionResource
    {
        try {
            $permission = $this->update($id, $data);

            return new PermissionResource($permission);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Permission not found');
        }
    }

    public function deletePermission(int $id): bool
    {
        try {
            $this->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Permission not found');
        }
    }

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource
    {
        $permission = $this->findById($permissionId)->resource;

        $permission->syncRoles($roleIds);

        return new PermissionResource($permission->fresh(['roles']));
    }
}
