<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

final class InvoiceService extends BaseService implements InvoiceServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        InvoiceRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    public function getFilteredInvoices(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($request);
    }

    public function getById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
    }

    public function findOrFail(int $id, array $columns = ['*']): Model|Invoice
    {
        return $this->repository->findOrFail($id, $columns);
    }

    public function create(array $data): Invoice|Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Invoice|Model
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
    }
}
