<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Http\Resources\Equipments\EquipmentCollection;

interface EquipmentServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): EquipmentCollection;

    public function getAll(array $columns = ['*']): EquipmentCollection;

    public function findById(int $id): EquipmentResource;

    public function createEquipment(array $data): EquipmentResource;

    public function updateEquipment(Equipment $equipment, array $data): EquipmentResource;

    public function deleteEquipment(Equipment $equipment): bool;
}
