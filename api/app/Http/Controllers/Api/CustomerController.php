<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Services\Contracts\CustomerServiceInterface;
use App\Http\Requests\Customers\CustomerStoreRequest;
use App\Http\Requests\Customers\IndexCustomerRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Requests\Customers\CustomerUpdateRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class CustomerController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

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

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        /** @var CustomerResource $customers */
        $customers = $this->customerService->getPaginated($perPage);

        return ApiResponse::success($customers);
    }

    /**
     * Store a newly created customer.
     *
     * @authenticated
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $customer = $this->customerService->createCustomer($request->validated());

        return ApiResponse::created($customer);
    }

    /**
     * Display the specified customer.
     *
     * @authenticated
     */
    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $customerResource = $this->customerService->findById($customer->id);

        return ApiResponse::success($customerResource);
    }

    /**
     * Update the specified customer.
     *
     * @authenticated
     */
    public function update(CustomerUpdateRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $customerResource = $this->customerService->updateCustomer($customer, $request->validated());

        return ApiResponse::success($customerResource);
    }

    /**
     * Remove the specified customer.
     *
     * @authenticated
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $this->customerService->deleteCustomer($customer);

        return ApiResponse::noContent('Customer deleted successfully');
    }
}
