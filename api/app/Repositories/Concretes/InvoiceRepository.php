<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Invoice;
use App\Filters\YearFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

final class InvoiceRepository extends QueryableRepository implements InvoiceRepositoryInterface
{
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->with(['customer'])
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getMergedAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    public function getDefaultSorts(): array
    {
        return ['-date'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'invoice_number', '-invoice_number',
            'customer_id', '-customer_id',
            'status', '-status',
            'date', '-date',
            'date_due', '-date_due',
            'subtotal', '-subtotal',
            'total', '-total',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'invoice_number',
            'customer_id',
            'status',
            'date',
            'date_due',
            'subtotal',
            'total_vat_0',
            'total_vat_9',
            'total_vat_21',
            'total',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return ['customer', 'items', 'payments', 'emails', 'activities'];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('invoice_number'),
            AllowedFilter::exact('customer_id'),
            AllowedFilter::exact('status'),
            AllowedFilter::partial('notes'),
            AllowedFilter::custom('date.year', new YearFilter()),
            AllowedFilter::custom('date_due.year', new YearFilter()),
            AllowedFilter::scope('date_due'),
            AllowedFilter::scope('between'),
            AllowedFilter::exact('subtotal'),
            AllowedFilter::exact('total'),
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): Invoice
    {
        return Invoice::query()
            ->with(['customer', 'items', 'payments', 'emails', 'activities.causer'])
            ->findOrFail($id, $columns);
    }

    protected function model(): string
    {
        return Invoice::class;
    }
}
