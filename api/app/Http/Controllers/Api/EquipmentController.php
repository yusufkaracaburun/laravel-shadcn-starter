<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Enums\EquipmentStatus;
use Illuminate\Http\JsonResponse;
use App\Services\Contracts\EquipmentServiceInterface;
use App\Http\Requests\Equipments\EquipmentIndexRequest;
use App\Http\Requests\Equipments\StoreEquipmentRequest;
use App\Http\Requests\Equipments\UpdateEquipmentRequest;

final class EquipmentController extends BaseApiController
{
    public function __construct(
        private readonly EquipmentServiceInterface $service,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EquipmentIndexRequest $request): JsonResponse
    {
        // $this->authorize('viewAny', Equipment::class);

        return $this->respondWithCollection(
            $this->service->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        // $this->authorize('create', Equipment::class);

        return $this->respondCreated(
            $this->service->create($request->validated()),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipment $equipment): JsonResponse
    {
        // $this->authorize('view', $equipment);

        return $this->respondWithResource(
            $this->service->findById($equipment->id),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        // $this->authorize('update', $equipment);

        return $this->respondWithResource(
            $this->service->update($equipment, $request->validated()),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        // $this->authorize('delete', $equipment);

        $this->service->delete($equipment);

        return $this->respondNoContent('Equipment deleted successfully');
    }

    /**
     * Get prerequisites for handling Equipment.
     */
    public function prerequisites(): JsonResponse
    {
        return $this->respondWithPrerequisites([
            'statuses' => EquipmentStatus::toArray(),
        ]);
    }
}
