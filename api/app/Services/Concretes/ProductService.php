<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;
use App\Services\Contracts\ProductServiceInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

final class ProductService extends BaseService implements ProductServiceInterface
{
    private readonly ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->productRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProductCollection
    {
        return new ProductCollection(
            $this->productRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): ProductCollection
    {
        return new ProductCollection(
            $this->productRepository->all($columns),
        );
    }

    public function findById(int $id): ProductResource
    {
        return new ProductResource(
            $this->productRepository->find($id),
        );
    }

    public function createProduct(array $data): ProductResource
    {
        return new ProductResource(
            parent::create($data),
        );
    }

    public function updateProduct(Product $product, array $data): ProductResource
    {
        return new ProductResource(
            parent::update($product, $data),
        );
    }

    public function deleteProduct(Product $product): bool
    {
        return parent::delete($product);
    }
}
