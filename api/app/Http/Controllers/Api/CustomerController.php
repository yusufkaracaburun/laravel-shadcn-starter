<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use App\Services\Contracts\CustomerServiceInterface;
use App\Http\Requests\Customers\CustomerStoreRequest;
use App\Http\Requests\Customers\IndexCustomerRequest;
use App\Http\Requests\Customers\CustomerUpdateRequest;

final class CustomerController extends BaseApiController
{
    public function __construct(
        private readonly CustomerServiceInterface $customerService,
    ) {}

    /**
     * Display a listing of customers with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/customers?filter[type]=business&sort=-created_at&include=contacts,invoices
     *
     * @authenticated
     */
    public function index(IndexCustomerRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        return $this->respondWithCollection(
            $this->customerService->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created customer.
     *
     * @authenticated
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        return $this->respondCreated(
            $this->customerService->create($request->validated()),
        );
    }

    /**
     * Display the specified customer.
     *
     * @authenticated
     */
    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        return $this->respondWithResource(
            $this->customerService->findById($customer->id),
        );
    }

    /**
     * Update the specified customer.
     *
     * @authenticated
     */
    public function update(CustomerUpdateRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        return $this->respondWithResource(
            $this->customerService->update($customer, $request->validated()),
        );
    }

    /**
     * Remove the specified customer.
     *
     * @authenticated
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $this->customerService->delete($customer);

        return $this->respondNoContent('Customer deleted successfully');
    }
}
