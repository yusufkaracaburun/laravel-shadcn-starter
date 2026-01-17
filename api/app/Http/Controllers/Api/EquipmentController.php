<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Enums\EquipmentStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Contracts\EquipmentServiceInterface;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Requests\Equipments\EquipmentIndexRequest;
use App\Http\Requests\Equipments\StoreEquipmentRequest;
use App\Http\Requests\Equipments\UpdateEquipmentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class EquipmentController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    public function __construct(
        private readonly EquipmentServiceInterface $service,
    ) {}

    /**
     * Get prerequisites for creating a new equipment.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        return ApiResponse::success([
            'statuses' => EquipmentStatus::toArray(),
        ]);
    }

    /**
     * Display a listing of equipment.
     *
     * @authenticated
     */
    public function index(EquipmentIndexRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', Equipment::class); // Uncomment when Policy is created

        $collection = $this->service->getPaginated($request);

        return ApiResponse::success($collection);
    }

    /**
     * Store a newly created equipment.
     *
     * @authenticated
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        // $this->authorize('create', Equipment::class);

        $equipmentResource = $this->service->createEquipment($request->validated());

        return ApiResponse::created($equipmentResource);
    }

    /**
     * Display the specified equipment.
     *
     * @authenticated
     */
    public function show(Equipment $equipment): JsonResponse
    {
        // $this->authorize('view', $equipment);

        $equipmentResource = $this->service->show($equipment);

        return ApiResponse::success($equipmentResource);
    }

    /**
     * Update the specified equipment.
     *
     * @authenticated
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        // $this->authorize('update', $equipment);

        $equipmentResource = $this->service->updateEquipment($equipment, $request->validated());

        return ApiResponse::success($equipmentResource);
    }

    /**
     * Remove the specified equipment.
     *
     * @authenticated
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        // $this->authorize('delete', $equipment);

        $this->service->deleteEquipment($equipment);

        return ApiResponse::noContent('Equipment deleted successfully');
    }
}
