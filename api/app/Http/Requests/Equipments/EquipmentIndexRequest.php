<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipments;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class EquipmentIndexRequest extends BaseIndexFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.name'          => 'sometimes|string|max:255',
                'filter.serial_number' => 'sometimes|string|max:255',
                'filter.type'          => 'sometimes|string|max:255',
                'filter.status'        => 'sometimes|string|in:active,maintenance,inactive',
            ],
        );
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'filter.name'          => 'name filter',
                'filter.serial_number' => 'serial number filter',
                'filter.type'          => 'type filter',
                'filter.status'        => 'status filter',
            ],
        );
    }
}
