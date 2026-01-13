<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Vehicle;
use App\Services\BaseService;
use Illuminate\Support\Arr;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;
use App\Services\Contracts\VehicleServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleService extends BaseService implements VehicleServiceInterface
{
    private readonly VehicleRepositoryInterface $vehicleRepository;

    public function __construct(
        VehicleRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->vehicleRepository = $repo;
    }

    public function getPaginated(int $perPage): VehicleCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->vehicleRepository->withRequest($request);

        $paginated = $this->vehicleRepository->query()->paginate($perPage);

        return new VehicleCollection($paginated);
    }

    public function findById(int $vehicleId): VehicleResource
    {
        try {
            $vehicle = $this->vehicleRepository->findOrFail($vehicleId);

            return new VehicleResource($vehicle);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Vehicle not found');
        }
    }

    public function createVehicle(array $data): VehicleResource
    {
        $vehicle = $this->vehicleRepository->create(Arr::except($data, ['drivers']));

        if (isset($data['drivers'])) {
            $vehicle->drivers()->sync($data['drivers']);
        }

        return new VehicleResource($vehicle->load('drivers'));
    }

    public function updateVehicle(Vehicle $vehicle, array $data): VehicleResource
    {
        try {
            $updated = $this->vehicleRepository->update($vehicle->id, Arr::except($data, ['drivers']));

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
            $this->vehicleRepository->delete($vehicle->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Vehicle not found');
        }
    }
}
