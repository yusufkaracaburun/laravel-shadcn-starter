<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Customer;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Spatie\QueryBuilder\QueryBuilderRequest;

final class CustomerRepository extends QueryableRepository implements CustomerRepositoryInterface
{
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

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

    public function findOrFail(int $id, array $columns = ['*']): Customer
    {
        return Customer::query()
            ->withCount(['invoices', 'contacts'])
            ->findOrFail($id, $columns);
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
