<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Equipment;
use App\Services\BaseService;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Services\Contracts\EquipmentServiceInterface;
use App\Http\Resources\Equipments\EquipmentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\EquipmentRepositoryInterface;

final class EquipmentService extends BaseService implements EquipmentServiceInterface
{
    private readonly EquipmentRepositoryInterface $repo;

    public function __construct(
        EquipmentRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->repo = $repo;
    }

    public function getPaginated(int $perPage): EquipmentCollection
    {
        $paginated = $this->repo->getPaginated($perPage);

        return new EquipmentCollection($paginated);
    }

    public function findById(int $id): EquipmentResource
    {
        try {
            $equipment = $this->repo->findOrFail($id);

            return new EquipmentResource($equipment);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Equipment not found');
        }
    }

    public function createEquipment(array $data): EquipmentResource
    {
        $equipment = $this->repo->create($data);

        return new EquipmentResource($equipment);
    }

    public function updateEquipment(Equipment $equipment, array $data): EquipmentResource
    {
        try {
            $updated = $this->repo->update($equipment->id, $data);

            return new EquipmentResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Equipment not found');
        }
    }

    public function deleteEquipment(Equipment $equipment): bool
    {
        try {
            $this->repo->delete($equipment->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Equipment not found');
        }
    }
}
