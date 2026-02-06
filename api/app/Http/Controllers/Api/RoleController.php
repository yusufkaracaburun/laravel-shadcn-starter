<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\IndexRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\Contracts\RoleServiceInterface;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class RoleController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    public function __construct(
        private readonly RoleServiceInterface $service,
    ) {}

    /**
     * Display a listing of roles.
     *
     * @authenticated
     */
    public function index(IndexRoleRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $collection = $this->service->getPaginated($request);

        return ApiResponse::success($collection);
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

        $roleResource = $this->service->createRole($data);

        if (!empty($permissionIds)) {
            $roleResource = $this->service->assignPermissions($roleResource->resource->id, $permissionIds);
        }

        return ApiResponse::created($roleResource);
    }

    /**
     * Display the specified role.
     *
     * @authenticated
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        $roleResource = $this->service->show($role);

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

        $roleResource = $this->service->updateRole($role, $data);

        if ($permissionIds !== null) {
            $roleResource = $this->service->assignPermissions($role->id, $permissionIds);
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

        $this->service->deleteRole($role);

        return ApiResponse::noContent('Role deleted successfully');
    }
}
