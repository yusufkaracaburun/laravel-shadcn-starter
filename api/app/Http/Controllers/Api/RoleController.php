<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Roles\IndexRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
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

        $perPage = (int) ($request->input('per_page', 15));

        $roles = $this->buildQuery(
            Role::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('is_system'),
            ],
            allowedSorts: [
                'id',
                'name',
                'is_system',
                'created_at',
            ],
            allowedIncludes: ['users', 'permissions', 'usersCount', 'permissionsCount']
        )->paginate($perPage);

        return ApiResponse::success(RoleResource::collection($roles));
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

        $role = Role::create($data);

        if (! empty($permissionIds)) {
            $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
            $role->permissions()->sync($permissions);
            $role->load('permissions');
        }

        return ApiResponse::created(new RoleResource($role));
    }

    /**
     * Display the specified role.
     *
     * @authenticated
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        $role->load(['users', 'permissions']);

        return ApiResponse::success(new RoleResource($role));
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

        $role->update($data);

        if ($permissionIds !== null) {
            $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
            $role->permissions()->sync($permissions);
            $role->load('permissions');
        }

        return ApiResponse::success(new RoleResource($role));
    }

    /**
     * Remove the specified role.
     *
     * @authenticated
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        return ApiResponse::noContent('Role deleted successfully');
    }
}
