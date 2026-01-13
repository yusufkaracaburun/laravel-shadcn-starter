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
            'id', '-id',
            'name', '-name',
            'status', '-status',
            'category', '-category',
            'start_date', '-start_date',
            'end_date', '-end_date',
            'progress', '-progress',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'category', '-category',
            'status', '-status',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
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

    public function findOrFail(int $id, array $columns = ['*']): Project
    {
        return Project::query()->findOrFail($id, $columns);
    }

    protected function model(): string
    {
        return Project::class;
    }
}
