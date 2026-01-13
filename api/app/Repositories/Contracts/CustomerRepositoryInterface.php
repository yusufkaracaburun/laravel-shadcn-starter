<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface CustomerRepositoryInterface extends QueryableRepositoryInterface
{
    public function getBusinessCustomers(int $perPage = 10): LengthAwarePaginator;

    public function getPrivateCustomers(int $perPage = 10): LengthAwarePaginator;

    public function findOrFail(int $id, array $columns = ['*']): Customer;
}
