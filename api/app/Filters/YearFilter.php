<?php

declare(strict_types=1);

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Year filter for filtering records by year from a date column.
 *
 * Supports two formats:
 * - "year" - Uses the default column (default: 'date')
 * - "column,year" - Specifies both column and year
 *
 * @example
 * ```php
 * // Use with default 'date' column
 * AllowedFilter::custom('year', new YearFilter())
 * // Usage: ?filter[year]=2024
 *
 * // Use with custom default column
 * AllowedFilter::custom('created_year', new YearFilter('created_at'))
 * // Usage: ?filter[created_year]=2024
 *
 * // Use with column,year format
 * AllowedFilter::custom('year', new YearFilter())
 * // Usage: ?filter[year]=date,2024 or ?filter[year]=created_at,2024
 * ```
 */
final readonly class YearFilter implements Filter
{
    /**
     * Create a new year filter instance.
     *
     * @param  string  $defaultColumn  The default column to filter by (default: 'date')
     */
    public function __construct(
        private string $defaultColumn = 'date'
    ) {}

    /**
     * Apply the filter logic.
     *
     * @param  Builder  $query  The Eloquent query builder
     * @param  mixed  $value  The filter value from the request (e.g., "2024" or "date,2024")
     * @param  string  $property  The property name used in filter (e.g., "year")
     */
    public function __invoke(Builder $query, mixed $value, string $property): Builder
    {
        $column = $this->defaultColumn;
        $year = $value;

        // Parse value if it contains a comma (column,year format)
        if (is_string($value) && str_contains($value, ',')) {
            [$col, $yr] = explode(',', $value, 2);
            $column = mb_trim($col);
            $year = mb_trim($yr);
        }

        // Cast year to integer
        $year = (int) $year;

        return $query->whereYear($column, $year);
    }
}
