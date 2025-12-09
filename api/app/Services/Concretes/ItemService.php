<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\ItemServiceInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ItemService extends BaseService implements ItemServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        ItemRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
    }

    public function getFilteredItems(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($request);
    }

    public function getById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }

    public function findOrFail(int $id, array $columns = ['*']): Model|Item
    {
        return $this->repository->findOrFail($id, $columns);
    }

    public function create(array $data): Item|Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Item|Model
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }
}
