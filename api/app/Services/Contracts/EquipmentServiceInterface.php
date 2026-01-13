<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Equipment;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Http\Resources\Equipments\EquipmentCollection;

interface EquipmentServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated equipment with QueryBuilder support.
     */
    public function getPaginated(int $perPage): EquipmentCollection;

    /**
     * Find equipment by ID.
     */
    public function findById(int $id): EquipmentResource;

    /**
     * Create new equipment.
     *
     * @param  array<string, mixed>  $data
     */
    public function createEquipment(array $data): EquipmentResource;

    /**
     * Update equipment.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateEquipment(Equipment $equipment, array $data): EquipmentResource;

    /**
     * Delete equipment.
     */
    public function deleteEquipment(Equipment $equipment): bool;
}
