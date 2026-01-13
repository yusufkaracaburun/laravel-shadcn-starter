<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;

interface CustomerServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): CustomerCollection;

    public function getAll(): CustomerCollection;

    public function findById(int $customerId, ?int $teamId = null): CustomerResource;

    public function createCustomer(array $data, ?int $teamId = null): CustomerResource;

    public function updateCustomer(Customer $customer, array $data, ?int $teamId = null): CustomerResource;

    public function deleteCustomer(Customer $customer): bool;
}
