<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Vehicle;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;

interface VehicleServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated vehicles with QueryBuilder support.
     */
    public function getPaginated(int $perPage): VehicleCollection;

    /**
     * Find a vehicle by ID.
     */
    public function findById(int $vehicleId): VehicleResource;

    /**
     * Create a new vehicle.
     *
     * @param  array<string, mixed>  $data
     */
    public function createVehicle(array $data): VehicleResource;

    /**
     * Update a vehicle.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateVehicle(Vehicle $vehicle, array $data): VehicleResource;

    /**
     * Delete a vehicle.
     */
    public function deleteVehicle(Vehicle $vehicle): bool;
}
