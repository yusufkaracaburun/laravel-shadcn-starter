<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Permission;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\PermissionRepositoryInterface;

final class PermissionRepository extends QueryableRepository implements PermissionRepositoryInterface
{
    protected function model(): string
    {
        return Permission::class;
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            ['name']
        );
    }

    public function getAllowedFields(): array
    {
        return ['id', 'name', 'guard_name'];
    }

    public function getAllowedIncludes(): array
    {
        return ['roles', 'users'];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::partial('name'),
            ]
        );
    }
}
