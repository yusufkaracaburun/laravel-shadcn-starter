<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for controllers that use Spatie Query Builder.
 *
 * Provides reusable methods for building queries with filtering, sorting, and includes.
 * Follows DRY principle and Strategy pattern.
 */
trait UsesQueryBuilder
{
    /**
     * Create a QueryBuilder instance for a model class.
     *
     * @param  class-string<Model>|Builder<Model>  $subject
     * @param  array<string>  $allowedFilters
     * @param  array<string>  $allowedSorts
     * @param  array<string>  $allowedIncludes
     * @param  array<string>  $allowedFields
     * @return QueryBuilder<Model>
     */
    protected function buildQuery(
        string|Builder $subject,
        array $allowedFilters = [],
        array $allowedSorts = [],
        array $allowedIncludes = [],
        array $allowedFields = []
    ): QueryBuilder {
        $query = QueryBuilder::for($subject);

        if ($allowedFilters !== []) {
            $query->allowedFilters($allowedFilters);
        }

        if ($allowedSorts !== []) {
            $query->allowedSorts($allowedSorts);
        }

        if ($allowedIncludes !== []) {
            $query->allowedIncludes($allowedIncludes);
        }

        if ($allowedFields !== []) {
            $query->allowedFields($allowedFields);
        }

        return $query;
    }
}
