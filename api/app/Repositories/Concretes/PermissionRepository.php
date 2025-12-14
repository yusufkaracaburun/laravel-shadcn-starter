<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Permission;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionRepository extends QueryableRepository implements PermissionRepositoryInterface
{
    public function getPermissions(): Collection
    {
        return $this->getFiltered();
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            'name',
        ];
    }

    public function getAllowedSorts(): array
    {
        return ['id', '-id', 'name', '-name'];
    }

    public function getAllowedIncludes(): array
    {
        return ['roles', 'users'];
    }

    public function getAllowedFields(): array
    {
        return ['id', 'name', 'guard_name'];
    }

    protected function model(): string
    {
        return Permission::class;
    }
}
