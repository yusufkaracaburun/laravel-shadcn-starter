<?php

declare(strict_types=1);

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final readonly class UserTeamFilter implements Filter
{
    /**
     * Apply the filter logic.
     *
     * Filters users that belong to a specific team ID,
     * either as a member or as an owner.
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $teamId = (int) $value;

        if ($teamId <= 0) {
            return;
        }

        $query->where(function (Builder $query) use ($teamId): void {
            $query->whereHas('teams', function (Builder $q) use ($teamId): void {
                $q->where('teams.id', $teamId);
            })->orWhereHas('ownedTeams', function (Builder $q) use ($teamId): void {
                $q->where('id', $teamId);
            });
        });
    }
}
