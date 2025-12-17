<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Base index form request class for paginated, filterable, and sortable API endpoints.
 *
 * This class provides common functionality for index/list endpoints including:
 * - Pagination (page, per_page)
 * - Filtering (filter array with various field filters)
 * - Sorting (sort field with ascending/descending)
 * - Includes (eager loading relationships)
 * - Sparse fieldsets (fields array for resource-specific field selection)
 *
 * @example
 * ```php
 * class IndexUserRequest extends BaseIndexFormRequest
 * {
 *     protected function getAllowedSorts(): array
 *     {
 *         return ['id', '-id', 'name', '-name'];
 *     }
 *
 *     protected function getAllowedIncludes(): array
 *     {
 *         return ['roles', 'permissions'];
 *     }
 *
 *     protected function getAllowedFields(): array
 *     {
 *         return ['id', 'name', 'email'];
 *     }
 * }
 * ```
 */
abstract class BaseIndexFormRequest extends BaseFormRequest
{
    /**
     * Get allowed sort fields (with ascending/descending variants).
     *
     * Override in child class to specify allowed sort fields.
     * Format: ['id', '-id', 'name', '-name'] where '-' prefix indicates descending.
     *
     * @return array<string>
     */
    abstract protected function getAllowedSorts(): array;

    /**
     * Get allowed includes (relationships to eager load).
     *
     * Override in child class to specify allowed relationships.
     *
     * @return array<string>
     */
    abstract protected function getAllowedIncludes(): array;

    /**
     * Get allowed fields for sparse fieldsets.
     *
     * Override in child class to specify allowed fields for field selection.
     *
     * @return array<string>
     */
    abstract protected function getAllowedFields(): array;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->paginationRules(),
            $this->filterRules(),
            $this->sortRules(),
            $this->includeRules(),
            $this->fieldRules(),
        );
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'per_page.integer' => 'Per page must be a number.',
                'per_page.min'     => 'Per page must be at least 1.',
                'per_page.max'     => 'Per page cannot exceed 100.',
                'page.integer'     => 'Page must be a number.',
                'page.min'         => 'Page must be at least 1.',
                'filter.array'     => 'Filter must be an array.',
                'sort.string'      => 'Sort must be a string.',
                'sort.in'          => 'Invalid sort field. Available: ' . implode(', ', $this->getAllowedSorts()),
                'include.string'   => 'Include must be a comma-separated string.',
                'fields.array'     => 'Fields must be an array.',
            ],
        );
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'per_page'      => 'items per page',
                'filter.search' => 'search term',
            ],
        );
    }

    /**
     * Get pagination validation rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function paginationRules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page'     => 'sometimes|integer|min:1',
        ];
    }

    /**
     * Get filter validation rules.
     *
     * Override in child class for specific filters.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return [
            'filter'         => 'sometimes|array',
            'filter.search'  => ['sometimes', 'string', 'max:255'],
            'filter.between' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}-\d{2},\d{4}-\d{2}-\d{2}$/'],
        ];
    }

    /**
     * Get sort validation rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function sortRules(): array
    {
        $allowedSorts = $this->getAllowedSorts();

        if ($allowedSorts === []) {
            return [];
        }

        return [
            'sort' => [
                'sometimes',
                'string',
                Rule::in($allowedSorts),
            ],
        ];
    }

    /**
     * Get include validation rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function includeRules(): array
    {
        $allowedIncludes = $this->getAllowedIncludes();

        if ($allowedIncludes === []) {
            return [];
        }

        return [
            'include' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) use ($allowedIncludes): void {
                    $requestedIncludes = array_map(trim(...), explode(',', $value));

                    foreach ($requestedIncludes as $include) {
                        if (!in_array($include, $allowedIncludes)) {
                            $fail("The include '{$include}' is not allowed. Allowed includes: " . implode(', ', $allowedIncludes));
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Get field validation rules (Sparse Fieldsets).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function fieldRules(): array
    {
        $allowedFields = $this->getAllowedFields();

        if ($allowedFields === []) {
            return [];
        }

        return [
            'fields' => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) use ($allowedFields): void {
                    // Validate each field regardless of resource key
                    foreach ($value as $fieldString) {
                        $requestedFields = array_map(trim(...), explode(',', $fieldString));

                        foreach ($requestedFields as $field) {
                            if (!in_array($field, $allowedFields)) {
                                $fail("The field '{$field}' is not allowed. Allowed fields: " . implode(', ', $allowedFields));
                            }
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->convertBooleanFilters();
    }

    /**
     * Convert string boolean values in filters to actual booleans.
     */
    protected function convertBooleanFilters(): void
    {
        if (!$this->has('filter')) {
            return;
        }

        $filters = $this->input('filter', []);
        $booleanFields = $this->getBooleanFilterFields();

        foreach ($booleanFields as $field) {
            if (isset($filters[$field]) && is_string($filters[$field])) {
                $boolValue = filter_var($filters[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($boolValue !== null) {
                    $filters[$field] = $boolValue;
                }
            }
        }

        $this->merge(['filter' => $filters]);
    }

    /**
     * Get fields that should be treated as booleans in filters.
     *
     * Override in child class to specify boolean fields.
     *
     * @return array<string>
     */
    protected function getBooleanFilterFields(): array
    {
        return [];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Set default values if not provided
        if (!$this->has('per_page')) {
            $this->merge(['per_page' => $this->getDefaultPerPage()]);
        }

        if (!$this->has('page')) {
            $this->merge(['page' => 1]);
        }

        // Remove legacy parameters after conversion
        $this->request->remove('sort_by');
        $this->request->remove('sort_order');
    }

    /**
     * Get default per page value.
     */
    protected function getDefaultPerPage(): int
    {
        return 10;
    }
}
