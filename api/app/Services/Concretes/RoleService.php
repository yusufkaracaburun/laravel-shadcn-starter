<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Role;
use App\Services\BaseService;
use InvalidArgumentException;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleCollection;
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
    public function getRoles(): RoleCollection
    {
        $roles = $this->getFiltered();

        return new RoleCollection($roles);
    }

    /**
     * Get all roles without pagination.
     */
    public function getAllRoles(): RoleCollection
    {
        $roles = $this->all();

        return new RoleCollection($roles);
    }

    /**
     * Get filtered roles with pagination.
     */
    public function getPaginated(int $perPage): RoleCollection
    {
        $paginated = $this->paginate($perPage);

        return new RoleCollection($paginated);
    }

    /**
     * Get role by id.
     */
    public function getRoleById(int $id): RoleResource
    {
        try {
            $role = $this->findOrFail($id);

            return new RoleResource($role);
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
    public function createRole(array $data): RoleResource
    {
        $role = $this->create($data);

        return new RoleResource($role);
    }

    /**
     * Update role.
     */
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
    public function assignPermissions(int $roleId, array $permissionIds): RoleResource
    {
        $role = $this->getRoleById($roleId)->resource;

        $role->syncPermissions($permissionIds);

        return new RoleResource($role->fresh(['permissions']));
    }

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): RoleCollection
    {
        $roles = $this->repo->getNonSystemRoles();

        return new RoleCollection($roles);
    }
}
