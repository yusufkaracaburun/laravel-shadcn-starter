<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface VehicleRepositoryInterface extends QueryableRepositoryInterface
{

}
