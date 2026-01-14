<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;

interface CustomerServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): CustomerCollection;

    public function getAll(array $columns = ['*']): CustomerCollection;

    public function findById(int $id): CustomerResource;

    public function create(array $data): CustomerResource;

    public function update(Model $model, array $data): CustomerResource;

    public function delete(Model $model): bool;
}
