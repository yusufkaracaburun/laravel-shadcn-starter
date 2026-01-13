<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface CustomerRepositoryInterface
{
    public function getBusinessCustomers(int $perPage = 9999): LengthAwarePaginator;
    public function getPrivateCustomers(int $perPage = 9999): LengthAwarePaginator;
}
