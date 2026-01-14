<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Filters\SearchFilter;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Concerns\ConfiguresQueryBuilder;

abstract class QueryableRepository extends BaseRepository implements QueryableRepositoryInterface
{
    use ConfiguresQueryBuilder;

    protected ?Request $request = null;

    final public function withRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model->newQuery(), $queryRequest)
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getMergedAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    final public function getFiltered(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    final public function paginateFiltered(array $columns = ['*']): LengthAwarePaginator
    {
        $perPage = ($this->request ?? request())->input('per_page', $this->DEFAULT_PER_PAGE);

        return $this->query()->paginate((int) $perPage, $columns);
    }

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
