<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\BaseService;
use InvalidArgumentException;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Roles\RoleCollection;
use App\Services\Contracts\RoleServiceInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RoleService extends BaseService implements RoleServiceInterface
{
    private readonly RoleRepositoryInterface $roleRepository;

    public function __construct(
        RoleRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->roleRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): RoleCollection
    {
        $response = $this->roleRepository->paginateFiltered($request, $columns);

        return new RoleCollection($response);
    }

    public function getAll(array $columns = ['*']): RoleCollection
    {
        $response = $this->roleRepository->all($columns);

        return new RoleCollection($response);
    }

    public function findById(int $id): RoleResource
    {
        try {
            $role = $this->findOrFail($id);

            return new RoleResource($role);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Role not found');
        }
    }

    public function findByName(string $name): ?Role
    {
        return $this->repo->findByName($name);
    }

    public function createRole(array $data): RoleResource
    {
        $role = $this->create($data);

        return new RoleResource($role);
    }

    public function updateRole(int $id, array $data): RoleResource
    {
        try {
            $role = $this->findOrFail($id);

            // Prevent updating system roles
            throw_if($role->is_system, InvalidArgumentException::class, 'Cannot update system roles');

            $updated = $this->update($id, $data);

            return new RoleResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Role not found');
        }
    }

    public function deleteRole(int $id): bool
    {
        try {
            $role = $this->findOrFail($id);

            // Prevent deleting system roles
            throw_if($role->is_system, InvalidArgumentException::class, 'Cannot delete system roles');

            $this->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Role not found');
        }
    }

    public function assignPermissions(int $roleId, array $permissionIds): RoleResource
    {
        $role = $this->findById($roleId)->resource;

        $role->syncPermissions($permissionIds);

        return new RoleResource($role->fresh(['permissions']));
    }

    public function getNonSystemRoles(): RoleCollection
    {
        $roles = $this->repo->getSystemRoles();

        return new RoleCollection($roles);
    }
}
