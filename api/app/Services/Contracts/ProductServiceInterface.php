<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;

interface ProductServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated products with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): ProductCollection;

    /**
     * Find an product by ID with relationships loaded.
     */
    public function findById(int $productId, ?int $teamId = null): ProductResource;

    /**
     * Create a new product with optional team context.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data, ?int $teamId = null): ProductResource;

    /**
     * Update an product by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(Product $product, array $data, ?int $teamId = null): ProductResource;

    /**
     * Delete an product by model instance.
     */
    public function deleteProduct(Product $product): bool;

    /**
     * Get all products without pagination.
     * Returns a collection of all products.
     */
    public function getAll(): ProductCollection;
}
