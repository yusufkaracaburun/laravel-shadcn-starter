<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Team;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamRepository extends QueryableRepository implements TeamRepositoryInterface
{
    protected function model(): string
    {
        return Team::class;
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
        return [
            'id',
            'name',
            'personal_team',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return [
            'owner',
            'users',
            AllowedInclude::count('usersCount'),
            'teamInvitations',
        ];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('name'),
                AllowedFilter::exact('personal_team'),
                AllowedFilter::exact('user_id'),
            ]
        );
    }
}
