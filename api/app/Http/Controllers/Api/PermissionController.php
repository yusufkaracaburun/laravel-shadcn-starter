<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
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

        $perPage = (int) ($request->input('per_page', 15));

        $permissions = $this->buildQuery(
            Permission::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('guard_name'),
            ],
            allowedSorts: [
                'id',
                'name',
                'guard_name',
            ],
            allowedIncludes: ['roles', 'users']
        )->paginate($perPage);

        return ApiResponse::success(PermissionResource::collection($permissions));
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

        $permission = Permission::create($data);

        if (! empty($roleIds)) {
            $roles = Role::query()->whereIn('id', $roleIds)->get();
            $permission->roles()->sync($roles);
            $permission->load('roles');
        }

        return ApiResponse::created(new PermissionResource($permission));
    }

    /**
     * Display the specified permission.
     *
     * @authenticated
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        $permission->load(['roles', 'users']);

        return ApiResponse::success(new PermissionResource($permission));
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

        $permission->update($data);

        if ($roleIds !== null) {
            $roles = Role::query()->whereIn('id', $roleIds)->get();
            $permission->roles()->sync($roles);
            $permission->load('roles');
        }

        return ApiResponse::success(new PermissionResource($permission));
    }

    /**
     * Remove the specified permission.
     *
     * @authenticated
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $permission->delete();

        return ApiResponse::noContent('Permission deleted successfully');
    }
}
