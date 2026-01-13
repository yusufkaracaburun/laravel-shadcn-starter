<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamRepository extends QueryableRepository implements TeamRepositoryInterface
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
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'personal_team'
        ];
    }

    public function getAllowedIncludes(): array
    {
        return [
            'owner',
            'users',
            AllowedInclude::count('usersCount'),
            'teamInvitations'
        ];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('personal_team'),
            AllowedFilter::exact('user_id'),
            AllowedFilter::scope('created_at'),
        ];
    }

    protected function model(): string
    {
        return Team::class;
    }
}
