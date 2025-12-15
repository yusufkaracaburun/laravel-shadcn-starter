<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Services\BaseServiceInterface;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\PermissionCollection;
use Illuminate\Database\Eloquent\Collection;

interface PermissionServiceInterface extends BaseServiceInterface
{
    public function getPermissions(): Collection|PermissionCollection;

    public function getAllPermissions(): Collection|PermissionCollection;

    public function getPaginated(int $perPage): PermissionCollection;

    public function findById(int $permissionId): PermissionResource;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPermission(array $data): PermissionResource;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePermission(int $id, array $data): PermissionResource;

    public function deletePermission(int $id): bool;

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource;
}
