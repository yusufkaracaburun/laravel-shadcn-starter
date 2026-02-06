<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Customers\CustomerCollection;

interface CustomerServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated customers with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(Request $request): CustomerCollection;

    /**
     * Show a customer with relationships loaded.
     */
    public function show(Customer $customer): CustomerResource;

    /**
     * Create a new customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomer(array $data): CustomerResource;

    /**
     * Update a customer by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCustomer(Customer $customer, array $data): CustomerResource;

    /**
     * Delete a customer by model instance.
     */
    public function deleteCustomer(Customer $customer): bool;

    /**
     * Get all customers without pagination.
     */
    public function getAll(): CustomerCollection;
}
