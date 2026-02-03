<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\BaseService;
use InvalidArgumentException;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Roles\RoleCollection;
use App\Services\Contracts\RoleServiceInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;

final class RoleService extends BaseService implements RoleServiceInterface
{
    private readonly RoleRepositoryInterface $repo;

    public function __construct(
        RoleRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(Request $request): RoleCollection
    {
        $paginated = $this->repo->withRequest($request)->paginateFiltered();

        return new RoleCollection($paginated);
    }

    public function show(Role $role): RoleResource
    {
        $role = $this->repo->findForShow($role);

        return new RoleResource($role);
    }

    public function createRole(array $data): RoleResource
    {
        $role = $this->repo->createWithRelationships($data);

        return new RoleResource($role);
    }

    public function updateRole(Role $role, array $data): RoleResource
    {
        // Prevent updating system roles
        throw_if($role->is_system, InvalidArgumentException::class, 'Cannot update system roles');

        $updated = $this->repo->updateWithRelationships($role, $data);

        return new RoleResource($updated);
    }

    public function deleteRole(Role $role): bool
    {
        // Prevent deleting system roles
        throw_if($role->is_system, InvalidArgumentException::class, 'Cannot delete system roles');

        return $this->repo->delete($role);
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(int $roleId, array $permissionIds): RoleResource
    {
        $role = $this->repo->findOrFail($roleId);
        $role = $this->repo->findForShow($role);

        $role->syncPermissions($permissionIds);

        return new RoleResource($role->fresh(['permissions']));
    }
}
