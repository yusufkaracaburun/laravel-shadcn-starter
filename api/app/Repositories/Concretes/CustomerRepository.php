<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Customer;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerRepository extends QueryableRepository implements CustomerRepositoryInterface
{
    protected function model(): string
    {
        return Customer::class;
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                'type',
                'name',
                'address',
                'zipcode',
                'city',
                'country',
                'email',
                'phone',
                'kvk_number',
                'vat_number',
                'iban_number',
            ]
        );
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'type',
            'name',
            'address',
            'zipcode',
            'city',
            'country',
            'email',
            'phone',
            'kvk_number',
            'vat_number',
            'iban_number',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return ['contacts', 'invoices'];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('type'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('phone'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('zipcode'),
                AllowedFilter::partial('country'),
                AllowedFilter::partial('kvk_number'),
                AllowedFilter::partial('vat_number'),
                AllowedFilter::partial('iban_number'),
            ]
        );
    }

    public function getBusinessCustomers(int $perPage = 9999): LengthAwarePaginator
    {
        return Customer::query()->business()->paginate($perPage);
    }

    public function getPrivateCustomers(int $perPage = 9999): LengthAwarePaginator
    {
        return Customer::query()->private()->paginate($perPage);
    }
}
