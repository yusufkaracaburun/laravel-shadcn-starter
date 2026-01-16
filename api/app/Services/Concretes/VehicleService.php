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
    private readonly VehicleRepositoryInterface $repo;

    public function __construct(
        VehicleRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(Request $request): VehicleCollection
    {
        $paginated = $this->repo->paginateFiltered($request);

        return new VehicleCollection($paginated);
    }

    public function show(Vehicle $vehicle): VehicleResource
    {
        $vehicle = $this->repo->findForShow($vehicle);

        return new VehicleResource($vehicle);
    }

    public function createVehicle(array $data): VehicleResource
    {
        $vehicle = $this->repo->createWithRelationships(Arr::except($data, ['drivers']));

        $this->syncDrivers($vehicle, $data);

        return new VehicleResource($vehicle);
    }

    public function updateVehicle(Vehicle $vehicle, array $data): VehicleResource
    {
        $updated = $this->repo->updateWithRelationships($vehicle, Arr::except($data, ['drivers']));

        $this->syncDrivers($updated, $data);

        return new VehicleResource($updated);
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        return $this->repo->delete($vehicle);
    }

    /**
     * Centralize drivers sync logic.
     */
    private function syncDrivers(Vehicle $vehicle, array $data): void
    {
        if (isset($data['drivers'])) {
            $vehicle->drivers()->sync($data['drivers']);
            $vehicle->load('drivers');
        }
    }
}
