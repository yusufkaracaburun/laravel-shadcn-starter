<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index permission request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for permission listing.
 */
final class IndexPermissionRequest extends BaseIndexFormRequest
{
    /**
     * Get custom messages specific to permissions.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'filter.id.integer'        => 'Filter ID must be a number.',
                'filter.id.exists'         => 'The selected permission does not exist.',
                'filter.name.string'       => 'Filter name must be a string.',
                'filter.guard_name.string' => 'Filter guard name must be a string.',
            ],
        );
    }

    /**
     * Get custom attributes specific to permissions.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'filter.id'         => 'permission ID filter',
                'filter.name'       => 'name filter',
                'filter.guard_name' => 'guard name filter',
            ],
        );
    }

    /**
     * Get filter validation rules specific to permissions.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.id'         => 'sometimes|integer|exists:permissions,id',
                'filter.name'       => 'sometimes|string|max:255',
                'filter.guard_name' => 'sometimes|string|max:255',
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
            'guard_name', '-guard_name',
        ];
    }

    /**
     * Get allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return ['roles', 'users'];
    }

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return ['id', 'name', 'guard_name'];
    }

    /**
     * Get fields that should be treated as booleans in filters.
     *
     * @return array<string>
     */
    protected function getBooleanFilterFields(): array
    {
        return [];
    }
}
