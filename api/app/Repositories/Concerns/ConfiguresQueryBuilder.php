<?php

declare(strict_types=1);

namespace App\Repositories\Concerns;

use Spatie\QueryBuilder\AllowedFilter;

trait ConfiguresQueryBuilder
{
    /**
     * Get default sorts for QueryBuilder.
     */
    public function getDefaultSorts(): array
    {
        return ['created_at'];
    }

    /**
     * Get allowed sorts for QueryBuilder.
     */
    public function getAllowedSorts(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get allowed fields for QueryBuilder.
     */
    public function getAllowedFields(): array
    {
        return ['id'];
    }

    /**
     * Get allowed includes for QueryBuilder.
     */
    public function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Get allowed filters for QueryBuilder.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::scope('created_at'),
            AllowedFilter::scope('updated_at'),
        ];
    }
}
