<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Role;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Roles\RoleCollection;
use Illuminate\Database\Eloquent\Collection;

interface RoleServiceInterface extends BaseServiceInterface
{
    /**
     * Get all roles.
     */
    public function getRoles(): Collection|RoleCollection;

    /**
     * Get all roles without pagination.
     */
    public function getAllRoles(): Collection|RoleCollection;

    /**
     * Get filtered roles with pagination.
     */
    public function getPaginated(int $perPage): RoleCollection;

    /**
     * Get role by id.
     */
    public function getRoleById(int $id): RoleResource;

    /**
     * Get role by name.
     */
    public function getRoleByName(string $name): ?Role;

    /**
     * Create new role.
     */
    public function createRole(array $data): RoleResource;

    /**
     * Update role.
     */
    public function updateRole(int $id, array $data): RoleResource;

    /**
     * Delete role.
     */
    public function deleteRole(int $id): bool;

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(int $roleId, array $permissionIds): RoleResource;

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): Collection|RoleCollection;
}
