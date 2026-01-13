<?php

declare(strict_types=1);

namespace App\Http\Resources\Vehicles; // Updated namespace

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class VehicleCollection extends ResourceCollection
{
    public $collects = VehicleResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
