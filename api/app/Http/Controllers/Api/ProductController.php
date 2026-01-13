<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Products\IndexProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Services\Contracts\ProductServiceInterface;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class ProductController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

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

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $products = $this->service->getPaginated($perPage);

        return ApiResponse::success($products);
    }

    /**
     * Store a newly created product.
     *
     * @authenticated
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->service->createProduct($request->validated());

        return ApiResponse::created($product);
    }

    /**
     * Display the specified product.
     *
     * @authenticated
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $productResource = $this->service->findById($product->id);

        return ApiResponse::success($productResource);
    }

    /**
     * Update the specified product.
     *
     * @authenticated
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $productResource = $this->service->updateProduct($product, $request->validated());

        return ApiResponse::success($productResource);
    }

    /**
     * Remove the specified product.
     *
     * @authenticated
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->service->deleteProduct($product);

        return ApiResponse::noContent('product deleted successfully');
    }
}
