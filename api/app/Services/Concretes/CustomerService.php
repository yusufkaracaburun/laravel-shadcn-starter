<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\CustomerServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerService extends BaseService implements CustomerServiceInterface
{
    public function __construct(
        CustomerRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    public function getFilteredCustomers(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($request);
    }

    public function getById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }

    public function findOrFail(int $id, array $columns = ['*']): Model|Customer
    {
        return $this->repository->findOrFail($id, $columns);
    }

    public function create(array $data): Model|Customer
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Model|Customer
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Customer not found');
        }
    }
}
