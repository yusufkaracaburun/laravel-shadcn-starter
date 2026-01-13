<?php

declare(strict_types=1);

namespace App\Http\Resources\Equipments;

use Illuminate\Http\Request;
use App\Enums\EquipmentStatus;
use App\Http\Resources\BaseResource;

final class EquipmentResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'serial_number'    => $this->serial_number,
            'type'             => $this->type,
            'status'           => $this->status,
            'status_formatted' => EquipmentStatus::toArrayItem($this->status),
            'image'            => $this->image,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
