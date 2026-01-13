<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Customer;
use App\Services\BaseService;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Services\Contracts\CustomerServiceInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerService extends BaseService implements CustomerServiceInterface
{
    private readonly CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->customerRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): CustomerCollection
    {
        return new CustomerCollection(
            $this->customerRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): CustomerCollection
    {
        return new CustomerCollection(
            $this->customerRepository->all($columns),
        );
    }

    public function findById(int $id): CustomerResource
    {
        return new CustomerResource(
            $this->customerRepository->find($id),
        );
    }

    public function createCustomer(array $data): CustomerResource
    {
        return new CustomerResource(
            parent::create($data),
        );
    }

    public function updateCustomer(Customer $customer, array $data): CustomerResource
    {
        return new CustomerResource(
            parent::update($customer, $data),
        );
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return parent::delete($customer);
    }
}
