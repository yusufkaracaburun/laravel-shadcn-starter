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
    protected function model(): string
    {
        return Role::class;
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            ['name', 'is_system']
        );
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'is_system',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return [
            'users',
            'permissions',
            AllowedInclude::count('usersCount'),
            AllowedInclude::count('permissionsCount'),
        ];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::partial('name'),
                AllowedFilter::exact('is_system'),
            ]
        );
    }

    public function findByName(string $name): ?Role
    {
        return $this->findByField('name', $name);
    }

    public function getSystemRoles(bool $is_system = false): Collection
    {
        return Role::query()->where('is_system', $is_system)->get();
    }

    public function getWebRolesFiltered(): Collection
    {
        return $this->query()
            ->where('guard_name', 'web')
            ->get(['id', 'name']);
    }
}
