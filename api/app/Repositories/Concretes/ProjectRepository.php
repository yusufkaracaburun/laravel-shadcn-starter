<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Project;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectRepository extends QueryableRepository implements ProjectRepositoryInterface
{
    protected function model(): string
    {
        return Project::class;
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                'name',
                'status',
                'category',
                'start_date',
                'end_date',
                'progress',
            ]
        );
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'category',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return ['team'];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('category'),
                AllowedFilter::exact('team_id'),
            ]
        );
    }
}
