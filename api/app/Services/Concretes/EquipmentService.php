<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Services\Contracts\EquipmentServiceInterface;
use App\Http\Resources\Equipments\EquipmentCollection;
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

    public function getPaginated(Request $request): EquipmentCollection
    {
        $paginated = $this->repo->paginateFiltered($request);

        return new EquipmentCollection($paginated);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        $equipment = $this->repo->findForShow($equipment);

        return new EquipmentResource($equipment);
    }

    public function createEquipment(array $data): EquipmentResource
    {
        $equipment = $this->repo->createWithRelationships($data);

        return new EquipmentResource($equipment);
    }

    public function updateEquipment(Equipment $equipment, array $data): EquipmentResource
    {
        $updated = $this->repo->updateWithRelationships($equipment, $data);

        return new EquipmentResource($updated);
    }

    public function deleteEquipment(Equipment $equipment): bool
    {
        return $this->repo->delete($equipment);
    }

    public function getAll(): EquipmentCollection
    {
        $equipments = $this->repo->all();

        return new EquipmentCollection($equipments);
    }
}
