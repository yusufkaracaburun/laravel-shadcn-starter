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

    final public function withRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

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

    public function getDefaultSorts(): array
    {
        return ['created_at'];
    }

    public function getAllowedSorts(): array
    {
        return ['id', '-id', 'created_at', '-created_at', 'updated_at', '-updated_at'];
    }

    public function getAllowedFields(): array
    {
        return ['id'];
    }

    public function getAllowedIncludes(): array
    {
        return [];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::scope('created_at'),
            AllowedFilter::scope('updated_at'),
        ];
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
