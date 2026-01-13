<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;

interface VehicleServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): VehicleCollection;

    public function getAll(array $columns = ['*']): VehicleCollection;

    public function findById(int $id): VehicleResource;

    public function createVehicle(array $data): VehicleResource;

    public function updateVehicle(Vehicle $vehicle, array $data): VehicleResource;

    public function deleteVehicle(Vehicle $vehicle): bool;
}
