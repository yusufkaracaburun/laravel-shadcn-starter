<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\BaseService;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\RoleServiceInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RoleService extends BaseService implements RoleServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly RoleRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    /**
     * Get all roles.
     */
    public function getRoles(): Collection
    {
        return $this->getFiltered();
    }

    /**
     * Get all roles without pagination.
     */
    public function getAllRoles(): Collection
    {
        return $this->all();
    }

    /**
     * Get filtered roles with pagination.
     */
    public function getFilteredRoles(?Request $request = null, int $perPage = 25): LengthAwarePaginator
    {
        return $this->paginate($perPage);
    }

    /**
     * Get role by id.
     */
    public function getRoleById(int $id): Model
    {
        try {
            return $this->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Role not found');
        }
    }

    /**
     * Get role by name.
     */
    public function getRoleByName(string $name): ?Role
    {
        return $this->repo->findByName($name);
    }

    /**
     * Create new role.
     */
    public function createRole(array $data): Role|Model
    {
        return $this->create($data);
    }

    /**
     * Update role.
     */
    public function updateRole(int $id, array $data): Role|Model
    {
        try {
            $role = $this->findOrFail($id);

            // Prevent updating system roles
            throw_if($role->is_system, InvalidArgumentException::class, 'Cannot update system roles');

            return $this->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Role not found');
        }
    }

    /**
     * Delete role.
     */
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

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(int $roleId, array $permissionIds): Role|Model
    {
        $role = $this->getRoleById($roleId);

        $role->syncPermissions($permissionIds);

        return $role->fresh(['permissions']);
    }

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): Collection
    {
        return $this->repo->getNonSystemRoles();
    }
}
