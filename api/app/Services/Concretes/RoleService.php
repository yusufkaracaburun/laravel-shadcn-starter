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

final class RoleService extends BaseService implements RoleServiceInterface
{
    private readonly RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->roleRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): RoleCollection
    {
        return new RoleCollection(
            $this->roleRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): RoleCollection
    {
        return new RoleCollection(
            $this->roleRepository->all($columns),
        );
    }

    public function findById(int $id): RoleResource
    {
        return new RoleResource(
            $this->roleRepository->find($id),
        );
    }

    public function findByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    public function createRole(array $data): RoleResource
    {
        return new RoleResource(
            parent::create($data),
        );
    }

    public function updateRole(int $id, array $data): RoleResource
    {
        $role = $this->roleRepository->find($id);

        // Prevent updating system roles
        throw_if($role->is_system, InvalidArgumentException::class, 'Cannot update system roles');

        return new RoleResource(
            parent::update($id, $data),
        );
    }

    public function deleteRole(int $id): bool
    {
        $role = $this->roleRepository->find($id);

        // Prevent deleting system roles
        throw_if($role->is_system, InvalidArgumentException::class, 'Cannot delete system roles');

        return parent::delete($id);
    }

    public function assignPermissions(int $roleId, array $permissionIds): RoleResource
    {
        $role = $this->roleRepository->find($roleId);

        $role->syncPermissions($permissionIds);

        return new RoleResource($role->fresh(['permissions']));
    }

    public function getNonSystemRoles(): RoleCollection
    {
        $roles = $this->roleRepository->getSystemRoles(false);

        return new RoleCollection($roles);
    }
}
