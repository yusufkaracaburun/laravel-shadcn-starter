<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PermissionServiceInterface extends BaseServiceInterface
{
    public function getPermissions(): Collection;

    public function getAllPermissions(): Collection;

    public function getFilteredPermissions(?Request $request = null, int $perPage = 25): LengthAwarePaginator;

    public function getPermissionById(int $id): Permission|Model;

    public function createPermission(array $data): Permission|Model;

    public function updatePermission(int $id, array $data): Permission|Model;

    public function deletePermission(int $id): bool;

    public function assignRoles(int $permissionId, array $roleIds): Permission|Model;
}
