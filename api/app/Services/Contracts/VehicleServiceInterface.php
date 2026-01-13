<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Vehicle;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;

interface VehicleServiceInterface extends BaseServiceInterface
{
    public function getPaginated(int $perPage, ?int $teamId = null): VehicleCollection;
    public function findById(int $vehicleId, ?int $teamId = null): VehicleResource;
    public function createVehicle(array $data, ?int $teamId = null): VehicleResource;
    public function updateVehicle(Vehicle $vehicle, array $data, ?int $teamId = null): VehicleResource;
    public function deleteVehicle(Vehicle $vehicle): bool;
}
