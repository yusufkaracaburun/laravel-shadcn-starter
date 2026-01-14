<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Enums\VehicleStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Vehicles\VehicleIndexRequest;
use App\Services\Contracts\VehicleServiceInterface;
use App\Http\Requests\Vehicles\UpdateVehicleRequest;

final class VehicleController extends BaseApiController
{
    public function __construct(
        private readonly VehicleServiceInterface $service,
    ) {}

    /**
     * Get prerequisites for creating a new vehicle.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        return $this->respondWithPrerequisites([
            'statuses' => VehicleStatus::toArray(),
        ]);
    }

    /**
     * Display a listing of vehicles.
     *
     * @authenticated
     */
    public function index(VehicleIndexRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', Vehicle::class); // Uncomment when Policy is created

        return $this->respondWithCollection(
            $this->service->getPaginatedByRequest(),
        );
    }

    /**
     * Store a newly created vehicle.
     *
     * @authenticated
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        // $this->authorize('create', Vehicle::class);

        return $this->respondCreated(
            $this->service->create($request->validated()),
        );
    }

    /**
     * Display the specified vehicle.
     *
     * @authenticated
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('view', $vehicle);

        return $this->respondWithResource(
            $this->service->find($vehicle->id),
        );
    }

    /**
     * Update the specified vehicle.
     *
     * @authenticated
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('update', $vehicle);

        return $this->respondWithResource(
            $this->service->update($vehicle, $request->validated()),
        );
    }

    /**
     * Remove the specified vehicle.
     *
     * @authenticated
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('delete', $vehicle);

        $this->service->delete($vehicle);

        return $this->respondNoContent('Vehicle deleted successfully');
    }
}
