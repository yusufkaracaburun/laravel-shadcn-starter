<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Item;
use App\Repositories\QueryableRepositoryInterface;

interface ItemRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Item;
}
