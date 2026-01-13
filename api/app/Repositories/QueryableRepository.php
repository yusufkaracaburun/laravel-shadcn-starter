<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Filters\SearchFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class QueryableRepository extends BaseRepository implements QueryableRepositoryInterface
{
    protected ?Request $request = null;

    /**
     * Get filtered, sorted, and included resources.
     */
    final public function getFiltered(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * Get a query builder instance with filters, sorts, and includes applied.
     */
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getMergedAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    /**
     * Get default sorts for this repository.
     */
    public function getDefaultSorts(): array
    {
        return [];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
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
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return [];
    }

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
     * Get paginated, filtered, sorted, and included resources.
     */
    final public function paginateFiltered(Request $request, array $columns = ['*']): LengthAwarePaginator
    {
        $this->withRequest($request);
        $perPage = $request->input('per_page', 10);

        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Set the request instance for query building
     */
    final public function withRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Merge allowed filters with an automatic search filter if the model has $searchable fields.
     */
    protected function getMergedAllowedFilters(): array
    {
        $filters = $this->getAllowedFilters();
        $modelClass = $this->model();

        if (property_exists($modelClass, 'searchable') && ! empty($modelClass::$searchable)) {
            $filters[] = AllowedFilter::custom('search', new SearchFilter($modelClass::$searchable));
        }

        return $filters;
    }
}
