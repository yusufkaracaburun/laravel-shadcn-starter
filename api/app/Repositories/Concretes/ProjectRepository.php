<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Project;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectRepository extends QueryableRepository implements ProjectRepositoryInterface
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
            'status',
            'category',
            'start_date',
            'end_date',
            'progress',
            'created_at',
            'updated_at',
        ];
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
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('category'),
            AllowedFilter::exact('team_id'),
            AllowedFilter::scope('created_at'),
        ];
    }

    protected function model(): string
    {
        return Project::class;
    }
}
