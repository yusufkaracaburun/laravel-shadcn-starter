<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Filters\SearchFilter;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\CustomerResource;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
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

        $perPage = (int) ($request->input('per_page', 15));

        $customers = $this->buildQuery(
            Customer::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('email'),
                AllowedFilter::exact('phone'),
                AllowedFilter::exact('city'),
                AllowedFilter::exact('country'),
                AllowedFilter::exact('kvk_number'),
                AllowedFilter::exact('vat_number'),
                AllowedFilter::exact('iban_number'),
                AllowedFilter::scope('created_at', 'created_at'),
                AllowedFilter::scope('between', 'created_at'),
                AllowedFilter::custom('search', new SearchFilter(Customer::$searchable)),
            ],
            allowedSorts: [
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
            ],
            allowedIncludes: ['contacts', 'primaryContact', 'invoices']
        )->paginate($perPage);

        return ApiResponse::success(CustomerResource::collection($customers));
    }

    /**
     * Store a newly created customer.
     *
     * @authenticated
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $customer = Customer::query()->create($request->validated());

        return ApiResponse::created(new CustomerResource($customer));
    }

    /**
     * Display the specified customer.
     *
     * @authenticated
     */
    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $customer->load(['contacts', 'primaryContact', 'invoices']);

        return ApiResponse::success(new CustomerResource($customer));
    }

    /**
     * Update the specified customer.
     *
     * @authenticated
     */
    public function update(CustomerUpdateRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return ApiResponse::success(new CustomerResource($customer));
    }

    /**
     * Remove the specified customer.
     *
     * @authenticated
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return ApiResponse::noContent('Customer deleted successfully');
    }
}
