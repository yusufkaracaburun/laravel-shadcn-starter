<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Repositories\QueryableRepositoryInterface;

interface ProductRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Product;
}
