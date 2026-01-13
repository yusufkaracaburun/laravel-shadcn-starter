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


    public function getDefaultSorts(): array
    {
        return ['-date'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id',
            'payment_number',
            'invoice_id',
            'customer_id',
            'status',
            'date',
            'paid_at',
            'amount',
            'method',
            'provider',
            'created_at',
            'updated_at',
        ];
    }

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

    public function getAllowedIncludes(): array
    {
        return ['invoice', 'customer'];
    }

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

    protected function model(): string
    {
        return Payment::class;
    }
}
