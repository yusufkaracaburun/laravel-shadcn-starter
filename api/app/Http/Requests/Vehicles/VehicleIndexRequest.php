<?php

declare(strict_types=1);

namespace App\Http\Requests\Vehicles;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class VehicleIndexRequest extends BaseIndexFormRequest
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
                'filter.make' => 'make filter',
                'filter.model' => 'model filter',
                'filter.year' => 'year filter',
                'filter.license_plate' => 'license plate filter',
                'filter.status' => 'status filter',
            ],
        );
    }

    /**
     * Get filter validation rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.make' => 'sometimes|string|max:255',
                'filter.model' => 'sometimes|string|max:255',
                'filter.year' => 'sometimes|integer',
                'filter.license_plate' => 'sometimes|string|max:20',
                'filter.status' => 'sometimes|string|in:active,maintenance,inactive',
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
            'make', '-make',
            'model', '-model',
            'year', '-year',
            'license_plate', '-license_plate',
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
        return ['drivers'];
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
            'make',
            'model',
            'year',
            'license_plate',
            'vin',
            'status',
            'created_at',
            'updated_at',
        ];
    }
}
