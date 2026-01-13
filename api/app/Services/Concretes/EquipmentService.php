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
    private readonly EquipmentRepositoryInterface $equipmentRepository;

    public function __construct(
        EquipmentRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->equipmentRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): EquipmentCollection
    {
        return new EquipmentCollection(
            $this->equipmentRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): EquipmentCollection
    {
        return new EquipmentCollection(
            $this->equipmentRepository->all($columns),
        );
    }

    public function findById(int $id): EquipmentResource
    {
        return new EquipmentResource(
            $this->equipmentRepository->find($id),
        );
    }

    public function createEquipment(array $data): EquipmentResource
    {
        return new EquipmentResource(
            parent::create($data),
        );
    }

    public function updateEquipment(Equipment $equipment, array $data): EquipmentResource
    {
        return new EquipmentResource(
            parent::update($equipment, $data),
        );
    }

    public function deleteEquipment(Equipment $equipment): bool
    {
        return parent::delete($equipment);
    }
}
