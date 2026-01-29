<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Customer;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedInclude;
use App\Repositories\QueryableRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerRepository extends QueryableRepository implements CustomerRepositoryInterface
{
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
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
        return [
            'contacts',
            'invoices',
            'primaryContact',
            AllowedInclude::count('invoicesCount'),
            AllowedInclude::count('contactsCount')
        ];
    }

    public function getAllowedFilters(): array
    {
        return [
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
            AllowedFilter::scope('created_at'),
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): Customer
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function getBusinessCustomers(int $perPage = 9999): LengthAwarePaginator
    {
        return Customer::query()->business()->paginate($perPage);
    }

    public function getPrivateCustomers(int $perPage = 9999): LengthAwarePaginator
    {
        return Customer::query()->private()->paginate($perPage);
    }

    protected function model(): string
    {
        return Customer::class;
    }
}
