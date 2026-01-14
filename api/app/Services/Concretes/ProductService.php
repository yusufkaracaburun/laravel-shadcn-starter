<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Services\Concerns\TransformsResources;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;
use App\Services\Contracts\ProductServiceInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

final class ProductService extends BaseService implements ProductServiceInterface
{
    use TransformsResources;

    public function __construct(
        ProductRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProductCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): ProductCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): ProductResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): ProductResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Product $model
     */
    public function update(Model $model, array $data): ProductResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Product $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return ProductResource::class;
    }

    protected function getCollectionClass(): string
    {
        return ProductCollection::class;
    }
}
