<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Payment;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentRepository extends QueryableRepository implements PaymentRepositoryInterface
{
    /**
     * Find a payment by ID with relations.
     */
    public function findOrFail(int $id, array $columns = ['*']): Payment
    {
        return Payment::query()
            ->with(['invoice', 'customer'])
            ->findOrFail($id, $columns);
    }

    /**
     * Base query with eager loads and filters.
     */
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->with(['customer', 'invoice'])
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getMergedAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    /**
     * Default sorting (latest payments first).
     */
    public function getDefaultSorts(): array
    {
        return ['-date'];
    }

    /**
     * Allowed sorts for QueryBuilder.
     */
    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'payment_number', '-payment_number',
            'invoice_id', '-invoice_id',
            'customer_id', '-customer_id',
            'status', '-status',
            'date', '-date',
            'paid_at', '-paid_at',
            'amount', '-amount',
            'method', '-method',
            'provider', '-provider',
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
            'invoice_id',
            'customer_id',
            'payment_number',
            'amount',
            'method',
            'provider',
            'provider_reference',
            'status',
            'date',
            'paid_at',
            'refunded_at',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Allowed includes.
     */
    public function getAllowedIncludes(): array
    {
        return ['invoice', 'customer'];
    }

    /**
     * Allowed filters for QueryBuilder.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('invoice_id'),
            AllowedFilter::exact('customer_id'),
            AllowedFilter::exact('status'),
            AllowedFilter::partial('payment_number'),
            AllowedFilter::partial('method'),
            AllowedFilter::partial('provider'),
            AllowedFilter::partial('provider_reference'),
            AllowedFilter::scope('date'),
            AllowedFilter::scope('paid_at'),
            AllowedFilter::scope('refunded_at'),
            AllowedFilter::exact('amount'),
        ];
    }

    /**
     * Base model for this repository.
     */
    protected function model(): string
    {
        return Payment::class;
    }
}
