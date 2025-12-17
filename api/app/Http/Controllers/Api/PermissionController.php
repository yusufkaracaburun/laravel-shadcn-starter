<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Requests\Permissions\IndexPermissionRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;
use App\Http\Requests\Permissions\UpdatePermissionRequest;

final class PermissionController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}

    /**
     * Display a listing of permissions with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/permissions?filter[name]=view&sort=-created_at&include=roles,users
     *
     * @authenticated
     */
    public function index(IndexPermissionRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $permissions = $this->permissionService->getPaginated($perPage);

        return ApiResponse::success($permissions);
    }

    /**
     * Store a newly created permission.
     *
     * @authenticated
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $this->authorize('create', Permission::class);

        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $permission = $this->permissionService->createPermission($data);

        if (!empty($roleIds)) {
            $permission = $this->permissionService->assignRoles($permission->resource->id, $roleIds);
        }

        return ApiResponse::created($permission);
    }

    /**
     * Display the specified permission.
     *
     * @authenticated
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        $permissionResource = $this->permissionService->findById($permission->id);

        return ApiResponse::success($permissionResource);
    }

    /**
     * Update the specified permission.
     *
     * @authenticated
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $this->authorize('update', $permission);

        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? null;
        unset($data['role_ids']);

        $permissionResource = $this->permissionService->updatePermission($permission->id, $data);

        if ($roleIds !== null) {
            $permissionResource = $this->permissionService->assignRoles($permission->id, $roleIds);
        }

        return ApiResponse::success($permissionResource);
    }

    /**
     * Remove the specified permission.
     *
     * @authenticated
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $this->permissionService->deletePermission($permission->id);

        return ApiResponse::noContent('Permission deleted successfully');
    }
}
