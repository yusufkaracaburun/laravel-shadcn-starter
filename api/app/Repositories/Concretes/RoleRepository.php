<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\RoleRepositoryInterface;

final class RoleRepository extends QueryableRepository implements RoleRepositoryInterface
{

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id',
            'name',
            'is_system',
            'created_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'is_system',
            'created_at',
            'updated_at'
        ];
    }

    public function getAllowedIncludes(): array
    {
        return [
            'users',
            'permissions',
            AllowedInclude::count('usersCount'),
            AllowedInclude::count('permissionsCount')
        ];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            'name',
            'is_system',
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): Role
    {
        return Role::query()->findOrFail($id, $columns);
    }

    /**
     * Find role for show endpoint with relationships loaded.
     */
    public function findForShow(Role $role): Role
    {
        return $this->loadRelationships($role);
    }

    /**
     * Create a new role and load relationships.
     */
    public function createWithRelationships(array $data): Role
    {
        /** @var Role $role */
        $role = parent::create($data);

        return $this->loadRelationships($role);
    }

    /**
     * Update role and load relationships.
     */
    public function updateWithRelationships(Role $role, array $data): Role
    {
        /** @var Role $updated */
        $updated = parent::update($role, $data);

        return $this->loadRelationships($updated);
    }

    public function findByName(string $name): ?Role
    {
        return $this->findByField('name', $name);
    }

    public function getSystemRoles(bool $is_system = false): Collection
    {
        return Role::query()->where('is_system', $is_system)->get();
    }

    protected function model(): string
    {
        return Role::class;
    }

    /**
     * Standardize relationship loading in one place.
     */
    private function loadRelationships(Model $role): Role
    {
        /** @var Role $role */
        return $role->load('permissions');
    }
}
