<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Vehicle;
use App\Repositories\QueryableRepositoryInterface;

interface VehicleRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Vehicle;
}
