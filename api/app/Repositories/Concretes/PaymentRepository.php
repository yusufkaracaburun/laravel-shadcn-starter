<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Payment;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentRepository extends QueryableRepository implements PaymentRepositoryInterface
{
    protected function model(): string
    {
        return Payment::class;
    }

    public function getDefaultSorts(): array
    {
        return ['-date'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                'payment_number',
                'invoice_id',
                'customer_id',
                'status',
                'date',
                'paid_at',
                'amount',
                'method',
                'provider',
            ]
        );
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
        return array_merge(
            parent::getAllowedFilters(),
            [
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
            ]
        );
    }
}
