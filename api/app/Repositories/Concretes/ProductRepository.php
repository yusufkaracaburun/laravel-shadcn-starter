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
            'id', '-id',
            'name', '-name',
            'unit_price', '-unit_price',
            'vat_rate', '-vat_rate',
            'unit', '-unit',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
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
    public function findOrFail(int $id, array $columns = ['*']): Product
    {
        return Product::query()
            ->findOrFail($id, $columns);
    }

    protected function model(): string
    {
        return Product::class;
    }
}
