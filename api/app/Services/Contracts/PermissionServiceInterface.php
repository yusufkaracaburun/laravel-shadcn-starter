<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Permissions\PermissionResource;
use App\Http\Resources\Permissions\PermissionCollection;

interface PermissionServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PermissionCollection;

    public function getAll(array $columns = ['*']): PermissionCollection;

    public function findById(int $id): PermissionResource;

    public function create(array $data): PermissionResource;

    public function update(Model $model, array $data): PermissionResource;

    public function delete(Model $model): bool;

    public function assignRoles(int $permissionId, array $roleIds): PermissionResource;
}
