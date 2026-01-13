<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class TeamIndexRequest extends BaseIndexFormRequest
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
                'filter.id.integer'  => 'Filter ID must be a number.',
                'filter.id.exists'   => 'The selected role does not exist.',
                'filter.name.string' => 'Filter name must be a string.',
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
                'filter.id'   => 'role ID filter',
                'filter.name' => 'name filter',
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
                'filter.id'   => 'sometimes|integer|exists:roles,id',
                'filter.name' => 'sometimes|string|max:255',
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
        return ['owner', 'users', 'usersCount', 'teamInvitations'];
    }

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
