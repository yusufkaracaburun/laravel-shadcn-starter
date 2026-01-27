<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index role request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for role listing.
 */
final class IndexRoleRequest extends BaseIndexFormRequest
{
    /**
     * Get custom messages specific to roles.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'filter.id.integer'        => 'Filter ID must be a number.',
                'filter.id.exists'         => 'The selected role does not exist.',
                'filter.name.string'       => 'Filter name must be a string.',
                'filter.is_system.boolean' => 'The system filter must be true or false.',
            ],
        );
    }

    /**
     * Get custom attributes specific to roles.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'filter.id'        => 'role ID filter',
                'filter.name'      => 'name filter',
                'filter.is_system' => 'system role filter',
            ],
        );
    }

    /**
     * Get filter validation rules specific to roles.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.id'        => 'sometimes|integer|exists:roles,id',
                'filter.name'      => 'sometimes|string|max:255',
                'filter.is_system' => 'sometimes|boolean',
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
            'is_system', '-is_system',
            'created_at', '-created_at',
        ];
    }

    /**
     * Get allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return ['users', 'permissions', 'usersCount', 'permissionsCount'];
    }

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return ['id', 'name', 'is_system', 'created_at'];
    }

    /**
     * Get fields that should be treated as booleans in filters.
     *
     * @return array<string>
     */
    protected function getBooleanFilterFields(): array
    {
        return ['is_system'];
    }
}
