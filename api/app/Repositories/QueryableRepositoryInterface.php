<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QueryableRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Set the request instance for query building
     */
    public function withRequest(Request $request): static;

    /**
     * Get a query builder instance with filters, sorts, and includes applied.
     */
    public function query(): QueryBuilder;

    /**
     * Get filtered, sorted, and included resources.
     */
    public function getFiltered(array $columns = ['*']): Collection;

    /**
     * Get paginated, filtered, sorted, and included resources.
     */
    public function paginateFiltered(Request $request, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array;

    /**
     * Get default sorts for this repository.
     */
    public function getDefaultSorts(): array;

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array;

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array;

    /**
     * Get allowed fields for this repository.
     */
    public function getAllowedFields(): array;
}
