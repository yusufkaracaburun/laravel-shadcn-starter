<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Customer;
use App\Services\BaseService;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Services\Contracts\CustomerServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerService extends BaseService implements CustomerServiceInterface
{
    private readonly CustomerRepositoryInterface $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
        $this->customerRepository = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): CustomerCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->customerRepository->withRequest($request);

        $paginated = $this->customerRepository->query()->paginate($perPage);

        return new CustomerCollection($paginated);
    }

    public function findById(int $customerId, ?int $teamId = null): CustomerResource
    {
        try {
            $customer = $this->customerRepository->findOrFail($customerId);

            return new CustomerResource($customer);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }

    public function createCustomer(array $data, ?int $teamId = null): CustomerResource
    {
        $customer = $this->customerRepository->create($data);

        return new CustomerResource($customer);
    }

    public function updateCustomer(Customer $customer, array $data, ?int $teamId = null): CustomerResource
    {
        try {
            $updated = $this->customerRepository->update($customer->id, $data);

            return new CustomerResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }

    public function deleteCustomer(Customer $customer): bool
    {
        try {
            $this->customerRepository->delete($customer->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }
}
