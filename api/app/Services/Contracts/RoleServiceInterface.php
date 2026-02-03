<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Roles\RoleCollection;

interface RoleServiceInterface extends BaseServiceInterface
{
    public function getPaginated(Request $request): RoleCollection;

    public function show(Role $role): RoleResource;

    /**
     * Create new role.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRole(array $data): RoleResource;

    /**
     * Update role.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRole(Role $role, array $data): RoleResource;

    /**
     * Delete role.
     */
    public function deleteRole(Role $role): bool;

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(int $roleId, array $permissionIds): RoleResource;
}
