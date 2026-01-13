<?php

declare(strict_types=1);

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\BaseCollection;

final class VehicleCollection extends BaseCollection
{
    protected string $resourceClass = VehicleResource::class;
}
