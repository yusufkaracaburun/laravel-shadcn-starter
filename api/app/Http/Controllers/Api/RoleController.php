<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Roles\IndexRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\Contracts\RoleServiceInterface;

final class RoleController extends BaseApiController
{
    public function __construct(
        private readonly RoleServiceInterface $roleService,
    ) {}

    /**
     * Display a listing of roles with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/roles?filter[name]=admin&sort=-created_at&include=users,permissions
     *
     * @authenticated
     */
    public function index(IndexRoleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $this->respondWithCollection(
            $this->roleService->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created role.
     *
     * @authenticated
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $data = $request->validated();
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        $role = $this->roleService->create($data);

        if (!empty($permissionIds)) {
            $role = $this->roleService->assignPermissions($role->resource->id, $permissionIds);
        }

        return $this->respondCreated($role);
    }

    /**
     * Display the specified role.
     *
     * @authenticated
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return $this->respondWithResource(
            $this->roleService->findById($role->id),
        );
    }

    /**
     * Update the specified role.
     *
     * @authenticated
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $data = $request->validated();
        $permissionIds = $data['permission_ids'] ?? null;
        unset($data['permission_ids']);

        $roleResource = $this->roleService->update($role, $data);

        if ($permissionIds !== null) {
            $roleResource = $this->roleService->assignPermissions($role->id, $permissionIds);
        }

        return $this->respondWithResource($roleResource);
    }

    /**
     * Remove the specified role.
     *
     * @authenticated
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $this->roleService->delete($role);

        return $this->respondNoContent('Role deleted successfully');
    }
}
