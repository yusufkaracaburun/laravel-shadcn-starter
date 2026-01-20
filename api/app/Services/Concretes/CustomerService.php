<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Customers\CustomerCollection;
use App\Services\Contracts\CustomerServiceInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerService extends BaseService implements CustomerServiceInterface
{
    private readonly CustomerRepositoryInterface $repo;

    public function __construct(
        CustomerRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(Request $request): CustomerCollection
    {
        $paginated = $this->repo->paginateFiltered($request);

        return new CustomerCollection($paginated);
    }

    public function show(Customer $customer): CustomerResource
    {
        $customer = $this->repo->findForShow($customer);

        return new CustomerResource($customer);
    }

    public function createCustomer(array $data): CustomerResource
    {
        $customer = $this->repo->createWithRelationships($data);

        return new CustomerResource($customer);
    }

    public function updateCustomer(Customer $customer, array $data): CustomerResource
    {
        $updated = $this->repo->updateWithRelationships($customer, $data);

        return new CustomerResource($updated);
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return $this->repo->delete($customer);
    }

    public function getAll(): CustomerCollection
    {
        $customers = $this->repo->all();

        return new CustomerCollection($customers);
    }

    public function getBusinessCustomers(): CustomerCollection
    {
        $customers = $this->repo->getBusinessCustomers();

        return new CustomerCollection($customers);
    }

    public function getPrivateCustomers(): CustomerCollection
    {
        $customers = $this->repo->getPrivateCustomers();

        return new CustomerCollection($customers);
    }
}
