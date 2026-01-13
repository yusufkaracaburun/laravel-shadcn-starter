<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Services\Contracts\VehicleServiceInterface;
use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Vehicles\VehicleIndexRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Requests\Vehicles\UpdateVehicleRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class VehicleController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly VehicleServiceInterface $vehicleService,
    ) {}

    /**
     * Get prerequisites for creating a new vehicle.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        return ApiResponse::success([
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

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? $request->query('per_page', 15)); // Handle per_page defaulting

        $vehicles = $this->vehicleService->getPaginated($perPage);

        return ApiResponse::success($vehicles);
    }

    /**
     * Store a newly created vehicle.
     *
     * @authenticated
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        // $this->authorize('create', Vehicle::class);

        $vehicle = $this->vehicleService->createVehicle($request->validated());

        return ApiResponse::created($vehicle);
    }

    /**
     * Display the specified vehicle.
     *
     * @authenticated
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('view', $vehicle);

        $vehicleResource = $this->vehicleService->findById($vehicle->id);

        return ApiResponse::success($vehicleResource);
    }

    /**
     * Update the specified vehicle.
     *
     * @authenticated
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('update', $vehicle);

        $vehicleResource = $this->vehicleService->updateVehicle($vehicle, $request->validated());

        return ApiResponse::success($vehicleResource);
    }

    /**
     * Remove the specified vehicle.
     *
     * @authenticated
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('delete', $vehicle);

        $this->vehicleService->deleteVehicle($vehicle);

        return ApiResponse::noContent('Vehicle deleted successfully');
    }
}
