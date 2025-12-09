<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Team;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamRepository extends QueryableRepository implements TeamRepositoryInterface
{
    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::scope('created_at'),
        ];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return [];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Get allowed fields for this repository.
     */
    public function getAllowedFields(): array
    {
        return [];
    }

    /**
     * Get default sorts for this repository.
     */
    public function getDefaultSorts(): array
    {
        return [];
    }

    /**
     * Get all teams for a specific user.
     */
    public function getTeamsForUser(int $userId): Collection
    {
        return $this->model
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->orWhere('user_id', $userId)
            ->get();
    }

    /**
     * Get personal team for a user.
     */
    public function getPersonalTeamForUser(int $userId): ?Team
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('personal_team', true)
            ->first();
    }

    /**
     * Get teams owned by a user.
     */
    public function getOwnedTeamsForUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Model binding.
     */
    protected function model(): string
    {
        return Team::class;
    }
}
