<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\RoleRepositoryInterface;

final class RoleRepository extends QueryableRepository implements RoleRepositoryInterface
{
    /**
     * Get all roles.
     */
    public function getRoles(): Collection
    {
        return $this->getFiltered();
    }

    /**
     * Get role by name.
     */
    public function findByName(string $name): ?Role
    {
        return $this->findByField('name', $name);
    }

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): Collection
    {
        return $this->model->where('is_system', false)->get();
    }

    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            'name',
            'is_system',
        ];
    }

    /**
     * Get default sorts for this repository.
     */
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return ['id', '-id', 'name', '-name', 'is_system', '-is_system', 'created_at', '-created_at'];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return ['users', 'permissions', AllowedInclude::count('usersCount'), AllowedInclude::count('permissionsCount')];
    }

    /**
     * Get allowed fields for this repository.
     */
    public function getAllowedFields(): array
    {
        return ['id', 'name', 'is_system', 'created_at', 'updated_at'];
    }

    /**
     * Specify Model class name.
     */
    protected function model(): string
    {
        return Role::class;
    }
}
