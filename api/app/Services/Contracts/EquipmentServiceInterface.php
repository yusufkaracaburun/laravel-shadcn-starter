<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Http\Resources\Equipments\EquipmentCollection;

interface EquipmentServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): EquipmentCollection;

    public function getAll(array $columns = ['*']): EquipmentCollection;

    public function findById(int $id): EquipmentResource;

    public function create(array $data): EquipmentResource;

    public function update(Model $model, array $data): EquipmentResource;

    public function delete(Model $model): bool;
}
