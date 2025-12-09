<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleServiceInterface extends BaseServiceInterface
{
    /**
     * Get all roles.
     */
    public function getRoles(): Collection;

    /**
     * Get all roles without pagination.
     */
    public function getAllRoles(): Collection;

    /**
     * Get filtered roles with pagination.
     */
    public function getFilteredRoles(?Request $request = null, int $perPage = 25): LengthAwarePaginator;

    /**
     * Get role by id.
     */
    public function getRoleById(int $id): Role|Model;

    /**
     * Get role by name.
     */
    public function getRoleByName(string $name): ?Role;

    /**
     * Create new role.
     */
    public function createRole(array $data): Role|Model;

    /**
     * Update role.
     */
    public function updateRole(int $id, array $data): Role|Model;

    /**
     * Delete role.
     */
    public function deleteRole(int $id): bool;

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(int $roleId, array $permissionIds): Role|Model;

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): Collection;
}
