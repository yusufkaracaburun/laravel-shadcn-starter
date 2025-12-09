<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;

/**
 * Trait providing date-based query scopes.
 *
 * Adds convenient scopes for filtering records by date ranges.
 */
trait HasDatesScope
{
    /**
     * Filter records by a single date (exact match).
     *
     * Example: ?filter[date]=2025-10-31
     */
    #[Scope]
    protected function date(Builder $query, string $value): Builder
    {
        return $query->whereDate('date', mb_trim($value));
    }

    /**
     * Filter records by a single due date (exact match).
     *
     * Example: ?filter[date_due]=2025-11-15
     */
    #[Scope]
    protected function dateDue(Builder $query, string $value): Builder
    {
        return $query->whereDate('date_due', mb_trim($value));
    }

    /**
     * Filter records by a date range.
     *
     * Examples:
     *   ?filter[between:date]=2025-10-01,2025-10-31
     *   ?filter[between:date_due]=2025-10-01,2025-10-31
     */
    #[Scope]
    protected function between(Builder $query, string $column, string|array $from, ?string $to = null): Builder
    {
        if (is_string($from) && str_contains($from, ',')) {
            [$from, $to] = array_map(trim(...), explode(',', $from));
        }

        if (is_array($from)) {
            [$from, $to] = [$from[0] ?? null, $from[1] ?? null];
        }

        return $query->whereBetween($column, [$from, $to]);
    }
}
