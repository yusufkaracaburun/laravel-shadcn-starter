<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Invoice;
use App\Filters\YearFilter;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

final class InvoiceRepository extends QueryableRepository implements InvoiceRepositoryInterface
{
    protected function model(): string
    {
        return Invoice::class;
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
                'invoice_number',
                'customer_id',
                'status',
                'date',
                'date_due',
                'subtotal',
                'total',
            ]
        );
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
        return array_merge(
            parent::getAllowedFilters(),
            [
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
            ]
        );
    }
}
