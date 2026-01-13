<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;

final class ProductRepository extends QueryableRepository implements ProductRepositoryInterface
{


    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id',
            'name',
            'unit_price',
            'vat_rate',
            'unit',
            'created_at',
            'updated_at',
        ];
    }

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

    public function getAllowedIncludes(): array
    {
        return ['invoiceLines'];
    }

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
    protected function model(): string
    {
        return Product::class;
    }
}
