<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Requests\Permissions\IndexPermissionRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;

final class PermissionController extends BaseApiController
{
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

        return $this->respondWithCollection(
            $this->permissionService->getPaginatedByRequest($request),
        );
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

        $permission = $this->permissionService->create($data);

        if (!empty($roleIds)) {
            $permission = $this->permissionService->assignRoles($permission->resource->id, $roleIds);
        }

        return $this->respondCreated($permission);
    }

    /**
     * Display the specified permission.
     *
     * @authenticated
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        return $this->respondWithResource(
            $this->permissionService->findById($permission->id),
        );
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

        $permissionResource = $this->permissionService->update($permission, $data);

        if ($roleIds !== null) {
            $permissionResource = $this->permissionService->assignRoles($permission->id, $roleIds);
        }

        return $this->respondWithResource($permissionResource);
    }

    /**
     * Remove the specified permission.
     *
     * @authenticated
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $this->permissionService->delete($permission);

        return $this->respondNoContent('Permission deleted successfully');
    }
}
