<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;

interface ProductServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProductCollection;

    public function getAll(): ProductCollection;

    public function findById(int $productId, ?int $teamId = null): ProductResource;

    public function createProduct(array $data, ?int $teamId = null): ProductResource;

    public function updateProduct(Product $product, array $data, ?int $teamId = null): ProductResource;

    public function deleteProduct(Product $product): bool;
}
