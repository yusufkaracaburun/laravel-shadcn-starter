<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Permissions\PermissionResource;
use App\Http\Resources\Permissions\PermissionCollection;

interface PermissionServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PermissionCollection;

    public function getAll(array $columns = ['*']): PermissionCollection;

    public function findById(int $id): PermissionResource;

    public function createPermission(array $data): PermissionResource;

    public function updatePermission(int $id, array $data): PermissionResource;

    public function deletePermission(int $id): bool;

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource;
}
