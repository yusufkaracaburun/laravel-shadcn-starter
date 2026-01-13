<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Enums\EquipmentStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
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
    use UsesQueryBuilder;

    public function __construct(
        private readonly EquipmentServiceInterface $service,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EquipmentIndexRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', Equipment::class);

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $equipments = $this->service->getPaginated($perPage);

        return ApiResponse::success($equipments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        // $this->authorize('create', Equipment::class);

        $equipment = $this->service->createEquipment($request->validated());

        return ApiResponse::created($equipment);
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipment $equipment): JsonResponse
    {
        // $this->authorize('view', $equipment);

        $equipmentResource = $this->service->findById($equipment->id);

        return ApiResponse::success($equipmentResource);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        // $this->authorize('update', $equipment);

        $equipmentResource = $this->service->updateEquipment($equipment, $request->validated());

        return ApiResponse::success($equipmentResource);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        // $this->authorize('delete', $equipment);

        $this->service->deleteEquipment($equipment);

        return ApiResponse::noContent('Equipment deleted successfully');
    }

    /**
     * Get prerequisites for handling Equipment.
     */
    public function prerequisites(): JsonResponse
    {
        return ApiResponse::success([
            'statuses' => EquipmentStatus::toArray(),
        ]);
    }
}
