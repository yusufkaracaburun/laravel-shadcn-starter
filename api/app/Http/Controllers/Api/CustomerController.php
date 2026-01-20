<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Enums\CustomerType;
use App\Enums\CustomerStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
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

    public function __construct(
        private readonly CustomerServiceInterface $service,
    ) {}

    /**
     * Get prerequisites for creating a new customer.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        return ApiResponse::success([
            'types'    => CustomerType::toArray(),
            'statuses' => CustomerStatus::toArray(),
        ]);
    }

    /**
     * Display a listing of customers.
     *
     * @authenticated
     */
    public function index(IndexCustomerRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', Customer::class);

        $cache = Customer::getCacheKeys();
        $collection = $this->cachedResponse($cache['index'], fn () => $this->service->getPaginated($request));

        return ApiResponse::success($collection);
    }

    /**
     * Store a newly created customer.
     *
     * @authenticated
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        // $this->authorize('create', Customer::class);

        $customerResource = $this->service->createCustomer($request->validated());

        return ApiResponse::created($customerResource);
    }

    /**
     * Display the specified customer.
     *
     * @authenticated
     */
    public function show(Customer $customer): JsonResponse
    {
        // $this->authorize('view', $customer);

        $cache = Customer::getCacheKeys();
        $customerResource = $this->cachedResponse(
            $cache['show'] . ".{$customer->id}",
            fn () => $this->service->show($customer),
        );

        return ApiResponse::success($customerResource);
    }

    /**
     * Update the specified customer.
     *
     * @authenticated
     */
    public function update(CustomerUpdateRequest $request, Customer $customer): JsonResponse
    {
        // $this->authorize('update', $customer);

        $customerResource = $this->service->updateCustomer($customer, $request->validated());

        return ApiResponse::success($customerResource);
    }

    /**
     * Remove the specified customer.
     *
     * @authenticated
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // $this->authorize('delete', $customer);

        $this->service->deleteCustomer($customer);

        return ApiResponse::noContent('Customer deleted successfully');
    }
}
