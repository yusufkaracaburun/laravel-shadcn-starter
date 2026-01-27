<?php

declare(strict_types=1);

namespace App\Http\Requests\Equipments;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class EquipmentIndexRequest extends BaseIndexFormRequest
{
    /**
     * Get custom attributes.
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
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
     * Get allowed sort fields.
     *
     * @return array<string>
     */
    protected function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'serial_number', '-serial_number',
            'type', '-type',
            'status', '-status',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    /**
     * Get allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'serial_number',
            'type',
            'status',
            'created_at',
            'updated_at',
        ];
    }
}
