<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Permissions\PermissionResource;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Resources\Permissions\PermissionCollection;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionService extends BaseService implements PermissionServiceInterface
{
    private readonly PermissionRepositoryInterface $permissionRepository;

    public function __construct(PermissionRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->permissionRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PermissionCollection
    {
        return new PermissionCollection(
            $this->permissionRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): PermissionCollection
    {
        return new PermissionCollection(
            $this->permissionRepository->all($columns),
        );
    }

    public function findById(int $id): PermissionResource
    {
        return new PermissionResource(
            $this->permissionRepository->find($id),
        );
    }

    public function createPermission(array $data): PermissionResource
    {
        return new PermissionResource(
            parent::create($data),
        );
    }

    public function updatePermission(int $id, array $data): PermissionResource
    {
        return new PermissionResource(
            parent::update($id, $data),
        );
    }

    public function deletePermission(int $id): bool
    {
        return parent::delete($id);
    }

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource
    {
        $permission = $this->permissionRepository->find($permissionId);

        $permission->syncRoles($roleIds);

        return new PermissionResource($permission->fresh(['roles']));
    }
}
