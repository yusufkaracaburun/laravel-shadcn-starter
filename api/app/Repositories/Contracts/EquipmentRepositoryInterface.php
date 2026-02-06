<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Equipment;
use App\Repositories\QueryableRepositoryInterface;

interface EquipmentRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Equipment;

    public function findForShow(Equipment $equipment): Equipment;

    public function createWithRelationships(array $data): Equipment;

    public function updateWithRelationships(Equipment $equipment, array $data): Equipment;
}
