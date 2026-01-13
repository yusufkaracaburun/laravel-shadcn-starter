<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;

interface ProductServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProductCollection;

    public function getAll(array $columns = ['*']): ProductCollection;

    public function findById(int $id): ProductResource;

    public function createProduct(array $data): ProductResource;

    public function updateProduct(Product $product, array $data): ProductResource;

    public function deleteProduct(Product $product): bool;
}
