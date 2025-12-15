<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Customer;
use App\Services\BaseServiceInterface;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;

interface CustomerServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated customers with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): CustomerCollection;

    /**
     * Find a customer by ID with relationships loaded.
     */
    public function findById(int $customerId, ?int $teamId = null): CustomerResource;

    /**
     * Create a new customer with optional team context.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomer(array $data, ?int $teamId = null): CustomerResource;

    /**
     * Update a customer by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCustomer(Customer $customer, array $data, ?int $teamId = null): CustomerResource;

    /**
     * Delete a customer by model instance.
     */
    public function deleteCustomer(Customer $customer): bool;
}
