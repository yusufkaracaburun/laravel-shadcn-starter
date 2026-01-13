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
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): RoleCollection;

    public function getAll(array $columns = ['*']): RoleCollection;

    public function getNonSystemRoles(): RoleCollection;

    public function findById(int $id): RoleResource;

    public function findByName(string $name): ?Role;

    public function createRole(array $data): RoleResource;

    public function updateRole(int $id, array $data): RoleResource;

    public function deleteRole(int $id): bool;

    public function assignPermissions(int $roleId, array $permissionIds): RoleResource;
}
