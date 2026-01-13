<?php

declare(strict_types=1);

namespace App\Http\Resources\Equipments;

use App\Http\Resources\BaseCollection;

final class EquipmentCollection extends BaseCollection
{
    protected string $resourceClass = EquipmentResource::class;
}
