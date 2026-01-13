<?php

declare(strict_types=1);

namespace App\Http\Resources\Vehicles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Users\UserResource;
use App\Enums\VehicleStatus;

final class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'license_plate' => $this->license_plate,
            'vin' => $this->vin,
            'status' => $this->status,
            'status_formatted' => VehicleStatus::toArrayItem($this->status),

            'drivers' => UserResource::collection($this->whenLoaded('drivers')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
