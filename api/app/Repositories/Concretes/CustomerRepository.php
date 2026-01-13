<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Customer;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerRepository extends QueryableRepository implements CustomerRepositoryInterface
{
    /**
     * Return all business customers, paginated.
     */
    public function getBusinessCustomers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->business()
            ->paginate($perPage);
    }

    /**
     * Base query with eager loads, counts and filters.
     */
    public function query(): QueryBuilder
    {
        return parent::query()
            ->with(['contacts'])
            ->withCount(['invoices']);
    }

    /**
     * Return all private customers, paginated.
     */
    public function getPrivateCustomers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->private()
            ->paginate($perPage);
    }

    /**
     * Default sort order.
     */
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    /**
     * Allowed filters.
     */
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
        ];
    }

    /**
     * Allowed sorts.
     */
    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'type', '-type',
            'name', '-name',
            'address', '-address',
            'zipcode', '-zipcode',
            'city', '-city',
            'country', '-country',
            'email', '-email',
            'phone', '-phone',
            'kvk_number', '-kvk_number',
            'vat_number', '-vat_number',
            'iban_number', '-iban_number',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    /**
     * Allowed fields.
     */
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

    /**
     * Allowed includes.
     */
    public function getAllowedIncludes(): array
    {
        return ['contacts', 'invoices'];
    }

    /**
     * Find a customer with related counts.
     */
    public function findOrFail(int $id, array $columns = ['*']): Customer
    {
        return Customer::query()
            ->withCount(['invoices', 'contacts'])
            ->findOrFail($id, $columns);
    }

    protected function model(): string
    {
        return Customer::class;
    }
}
