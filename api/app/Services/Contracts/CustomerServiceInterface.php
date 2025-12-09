<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerServiceInterface extends BaseServiceInterface
{
    public function getFilteredCustomers(Request $request): LengthAwarePaginator;

    public function getById(int $id): Model|Customer|null;

    public function create(array $data): Model|Customer;

    public function update(int $id, array $data): Model|Customer;

    public function delete(int $id): bool;
}
