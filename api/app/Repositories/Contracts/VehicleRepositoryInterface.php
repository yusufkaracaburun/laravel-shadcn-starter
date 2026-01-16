<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface VehicleRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Vehicle;

    public function findForShow(Vehicle $vehicle): Vehicle;

    public function createWithRelationships(array $data): Vehicle;

    public function updateWithRelationships(Vehicle $vehicle, array $data): Vehicle;
}
