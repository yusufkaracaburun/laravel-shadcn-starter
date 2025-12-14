<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentService extends BaseService implements PaymentServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        PaymentRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    public function getFilteredPayments(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($request);
    }

    public function getById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }

    public function findOrFail(int $id, array $columns = ['*']): Model|Payment
    {
        return $this->repository->findOrFail($id, $columns);
    }

    public function create(array $data): Payment|Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Payment|Model
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }
}
