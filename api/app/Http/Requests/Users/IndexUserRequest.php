<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index user request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for user listing.
 */
final class IndexUserRequest extends BaseIndexFormRequest
{
    /**
     * Get custom messages specific to users.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'filter.id.integer'             => 'Filter ID must be a number.',
                'filter.id.exists'              => 'The selected user does not exist.',
                'filter.name.string'            => 'Filter name must be a string.',
                'filter.email.email'            => 'The email filter must be a valid email address.',
                'filter.email_verified_at.date' => 'The email verified at filter must be a valid date.',
                'filter.created_at.date'        => 'The created at filter must be a valid date.',
            ],
        );
    }

    /**
     * Get custom attributes specific to users.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            array(
                'filter.id'                => 'user ID filter',
                'filter.name'              => 'name filter',
                'filter.email'             => 'email filter',
                'filter.status'            => 'status filter',
                'filter.email_verified_at' => 'email verified filter',
                'filter.created_at'        => 'created at filter',
            ),
        );
    }

    /**
     * Get filter validation rules specific to users.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.id'                => 'sometimes|integer|exists:users,id',
                'filter.name'              => 'sometimes|string|max:255',
                'filter.email'             => 'sometimes|email|max:255',
                'filter.status'            => 'sometimes',
                'filter.email_verified_at' => 'sometimes|date',
                'filter.created_at'        => 'sometimes|date',
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
            'status', '-status',
            'email', '-email',
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
        return ['companies', 'invoices', 'roles', 'permissions'];
    }

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return ['id', 'name', 'status', 'email', 'email_verified_at', 'created_at', 'updated_at'];
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
