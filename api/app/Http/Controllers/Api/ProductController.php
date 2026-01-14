<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Products\IndexProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Services\Contracts\ProductServiceInterface;
use App\Http\Requests\Products\UpdateProductRequest;

final class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductServiceInterface $service,
    ) {}

    /**
     * Display a listing of products with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/products?filter[name]=test&sort=-created_at&include=invoiceLines
     *
     * @authenticated
     */
    public function index(IndexProductRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->respondWithCollection(
            $this->service->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created product.
     *
     * @authenticated
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        return $this->respondCreated(
            $this->service->create($request->validated()),
        );
    }

    /**
     * Display the specified product.
     *
     * @authenticated
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return $this->respondWithResource(
            $this->service->findById($product->id),
        );
    }

    /**
     * Update the specified product.
     *
     * @authenticated
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        return $this->respondWithResource(
            $this->service->update($product, $request->validated()),
        );
    }

    /**
     * Remove the specified product.
     *
     * @authenticated
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->service->delete($product);

        return $this->respondNoContent('Product deleted successfully');
    }
}
