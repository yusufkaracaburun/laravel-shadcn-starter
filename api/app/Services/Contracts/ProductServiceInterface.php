<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Products\ProductResource;
use App\Http\Resources\Products\ProductCollection;

interface ProductServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProductCollection;

    public function getAll(array $columns = ['*']): ProductCollection;

    public function findById(int $id): ProductResource;

    public function create(array $data): ProductResource;

    public function update(Model $model, array $data): ProductResource;

    public function delete(Model $model): bool;
}
