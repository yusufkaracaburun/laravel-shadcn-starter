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
    /**
     * Return paginated invoices with filters and sorting applied.
     */
    public function getInvoices(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    /**
     * Define the base query for invoices using Spatie QueryBuilder.
     */
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

    /**
     * Default sorting: latest invoice first.
     */
    public function getDefaultSorts(): array
    {
        return ['-date'];
    }

    /**
     * Allowed sorts for invoices.
     */
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

    /**
     * Allowed fields for sparse fieldsets.
     */
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

    /**
     * Allowed includes for eager loading.
     */
    public function getAllowedIncludes(): array
    {
        return ['customer', 'items', 'payments', 'emails', 'activities'];
    }

    /**
     * Allowed filters for invoice listing.
     */
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

    /**
     * Find an invoice by ID with related models.
     */
    public function findOrFail(int $id, array $columns = ['*']): Invoice
    {
        return Invoice::query()
            ->with(['customer', 'items', 'payments', 'emails', 'activities.causer'])
            ->findOrFail($id, $columns);
    }

    /**
     * Define the associated Eloquent model.
     */
    protected function model(): string
    {
        return Invoice::class;
    }
}
