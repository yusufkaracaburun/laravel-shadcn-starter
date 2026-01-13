<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;
use App\Services\Contracts\VehicleServiceInterface;
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

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): VehicleCollection
    {
        $response = $this->vehicleRepository->paginateFiltered($request, $columns);

        return new VehicleCollection($response);
    }

    public function getAll(array $columns = ['*']): VehicleCollection
    {
        $response = $this->vehicleRepository->all($columns);

        return new VehicleCollection($response);
    }

    public function findById(int $id): VehicleResource
    {
        $response = $this->vehicleRepository->find($id);

        return new VehicleResource($response);
    }

    public function createVehicle(array $data): VehicleResource
    {
        $vehicle = parent::create(Arr::except($data, ['drivers']));

        if (isset($data['drivers'])) {
            $vehicle->drivers()->sync($data['drivers']);
        }

        return new VehicleResource($vehicle->load('drivers'));
    }

    public function updateVehicle(Vehicle $vehicle, array $data): VehicleResource
    {
        $updated = parent::update($vehicle, Arr::except($data, ['drivers']));

        if (isset($data['drivers'])) {
            $updated->drivers()->sync($data['drivers']);
        }

        return new VehicleResource($updated->load('drivers'));
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        return parent::delete($vehicle);
    }
}
