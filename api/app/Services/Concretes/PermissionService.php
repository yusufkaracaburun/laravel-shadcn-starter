<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionService extends BaseService implements PermissionServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        PermissionRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    public function getPermissions(): Collection
    {
        return $this->getFiltered();
    }

    public function getAllPermissions(): Collection
    {
        return $this->all();
    }

    public function getFilteredPermissions(?Request $request = null, int $perPage = 25): LengthAwarePaginator
    {
        return $this->paginate($perPage);
    }

    public function getPermissionById(int $id): Model
    {
        try {
            return $this->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Permission not found');
        }
    }

    public function createPermission(array $data): Permission|Model
    {
        return $this->create($data);
    }

    public function updatePermission(int $id, array $data): Permission|Model
    {
        try {
            return $this->update($id, $data);
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

    public function assignRoles(int $permissionId, array $roleIds): Permission|Model
    {
        $permission = $this->getPermissionById($permissionId);

        $permission->syncRoles($roleIds);

        return $permission->fresh();
    }
}
