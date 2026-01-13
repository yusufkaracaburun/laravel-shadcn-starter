<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

final class RoleRepository extends QueryableRepository implements RoleRepositoryInterface
{
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'is_system', '-is_system',
            'created_at', '-created_at'
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
}
