<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Item;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use App\Repositories\Contracts\ItemRepositoryInterface;

final class ItemRepository extends QueryableRepository implements ItemRepositoryInterface
{
    /**
     * Find an item by ID with relations.
     */
    public function findOrFail(int $id, array $columns = ['*']): Item
    {
        return Item::query()
            ->findOrFail($id, $columns);
    }

    /**
     * Base query with eager loads and filters.
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
     * Default sorting (by name asc).
     */
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    /**
     * Allowed sorts for QueryBuilder.
     */
    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'unit_price', '-unit_price',
            'vat_rate', '-vat_rate',
            'unit', '-unit',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    /**
     * Allowed sparse fieldsets.
     */
    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'description',
            'unit_price',
            'vat_rate',
            'unit',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Allowed includes.
     */
    public function getAllowedIncludes(): array
    {
        return ['invoiceLines'];
    }

    /**
     * Allowed filters for QueryBuilder.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('description'),
            AllowedFilter::exact('unit'),
            AllowedFilter::exact('vat_rate'),
            AllowedFilter::exact('unit_price'),
        ];
    }

    /**
     * Base model for this repository.
     */
    protected function model(): string
    {
        return Item::class;
    }
}
