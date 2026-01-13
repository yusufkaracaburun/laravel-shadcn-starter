<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use App\Http\Resources\Permissions\PermissionResource;
use App\Http\Resources\Permissions\PermissionCollection;

interface PermissionServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PermissionCollection;

    public function getAll(): PermissionCollection;

    public function findById(int $permissionId): PermissionResource;

    public function createPermission(array $data): PermissionResource;

    public function updatePermission(int $id, array $data): PermissionResource;

    public function deletePermission(int $id): bool;

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource;
}
