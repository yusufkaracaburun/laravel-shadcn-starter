<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;

interface VehicleServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(array $columns = ['*']): VehicleCollection;

    public function getAll(array $columns = ['*']): VehicleCollection;

    public function find(int|string $id): VehicleResource;

    public function create(array $data): VehicleResource;

    public function update(Vehicle $model, array $data): VehicleResource;

    public function delete(Vehicle $model): bool;
}
