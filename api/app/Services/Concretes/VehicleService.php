<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Services\BaseService;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;
use App\Services\Contracts\VehicleServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleService extends BaseService implements VehicleServiceInterface
{
    private readonly VehicleRepositoryInterface $repo;

    public function __construct(
        VehicleRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): VehicleCollection
    {
        $paginated = $this->repo->paginateFiltered($perPage, $teamId);

        return new VehicleCollection($paginated);
    }

    public function findById(int $vehicleId, ?int $teamId = null): VehicleResource
    {
        try {
            $vehicle = $this->repo->findById($vehicleId, $teamId);

            return new VehicleResource($vehicle);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Vehicle not found');
        }
    }

    public function createVehicle(array $data, ?int $teamId = null): VehicleResource
    {
        $vehicle = $this->repo->createVehicle(Arr::except($data, ['drivers']), $teamId);

        if (isset($data['drivers'])) {
            $vehicle->drivers()->sync($data['drivers']);
        }

        return new VehicleResource($vehicle->load('drivers'));
    }

    public function updateVehicle(Vehicle $vehicle, array $data, ?int $teamId = null): VehicleResource
    {
        try {
            $updated = $this->repo->updateVehicle($vehicle->id, Arr::except($data, ['drivers']), $teamId);

            if (isset($data['drivers'])) {
                $updated->drivers()->sync($data['drivers']);
            }

            return new VehicleResource($updated->load('drivers'));
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Vehicle not found');
        }
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        try {
            $this->repo->deleteVehicle($vehicle);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Vehicle not found');
        }
    }
}
