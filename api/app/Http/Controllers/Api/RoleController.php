<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\IndexRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\Contracts\RoleServiceInterface;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class RoleController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

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

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $roles = $this->roleService->getPaginated($perPage);

        return ApiResponse::success($roles);
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

        $role = $this->roleService->createRole($data);

        if (!empty($permissionIds)) {
            $role = $this->roleService->assignPermissions($role->resource->id, $permissionIds);
        }

        return ApiResponse::created($role);
    }

    /**
     * Display the specified role.
     *
     * @authenticated
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        $roleResource = $this->roleService->getRoleById($role->id);

        return ApiResponse::success($roleResource);
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

        $roleResource = $this->roleService->updateRole($role->id, $data);

        if ($permissionIds !== null) {
            $roleResource = $this->roleService->assignPermissions($role->id, $permissionIds);
        }

        return ApiResponse::success($roleResource);
    }

    /**
     * Remove the specified role.
     *
     * @authenticated
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $this->roleService->deleteRole($role->id);

        return ApiResponse::noContent('Role deleted successfully');
    }
}
