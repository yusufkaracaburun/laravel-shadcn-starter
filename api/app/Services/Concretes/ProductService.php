<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Product;
use App\Services\BaseService;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;

final class ProductService extends BaseService implements ProductServiceInterface
{
    private readonly ProductRepositoryInterface $repo;

    public function __construct(
        ProductRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): ProductCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->repo->withRequest($request);

        $paginated = $this->repo->query()->paginate($perPage);

        return new ProductCollection($paginated);
    }

    public function findById(int $productId, ?int $teamId = null): ProductResource
    {
        try {
            $product = $this->repo->findOrFail($productId);

            return new ProductResource($product);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Product not found');
        }
    }

    public function createProduct(array $data, ?int $teamId = null): ProductResource
    {
        $product = $this->repo->create($data);

        return new ProductResource($product);
    }

    public function updateProduct(Product $product, array $data, ?int $teamId = null): ProductResource
    {
        try {
            $updated = $this->repo->update($product->id, $data);

            return new ProductResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Product not found');
        }
    }

    public function deleteProduct(Product $product): bool
    {
        try {
            $this->repo->delete($product->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Product not found');
        }
    }

    public function getAll(): ProductCollection
    {
        $products = $this->repo->all();

        return new ProductCollection($products);
    }
}
