<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index product request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for product listing.
 */
final class IndexProductRequest extends BaseIndexFormRequest
{
    /**
     * Custom validation messages specific to products.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'filter.id.exists'          => 'The selected product does not exist.',
            'filter.name.string'        => 'The name must be a valid string.',
            'filter.unit.string'        => 'The unit must be a valid string.',
            'filter.description.string' => 'The description must be a valid string.',
            'filter.unit_price.numeric' => 'The unit price must be a valid number.',
            'filter.vat_rate.numeric'   => 'The VAT rate must be a valid number.',
            'filter.created_at.date'    => 'The created at filter must be a valid date.',
            'filter.updated_at.date'    => 'The updated at filter must be a valid date.',
            'filter.between'            => 'The between filter must include a valid start and end date (e.g. 2025-01-01,2025-01-31).',
        ]);
    }

    /**
     * Custom attribute names for better error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'filter.id'          => 'product ID',
            'filter.name'        => 'name',
            'filter.description' => 'description',
            'filter.unit_price'  => 'unit price',
            'filter.vat_rate'    => 'VAT rate',
            'filter.unit'        => 'unit',
            'filter.created_at'  => 'created at',
            'filter.updated_at'  => 'updated at',
            'filter.between'     => 'date range',
        ]);
    }

    /**
     * Filter validation rules specific to products.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(parent::filterRules(), [
            // Common scalar filters
            'filter.name'        => ['sometimes', 'string', 'max:255'],
            'filter.description' => ['sometimes', 'string'],
            'filter.unit'        => ['sometimes', 'string', 'max:50'],

            // Numeric filters
            'filter.unit_price' => ['sometimes', 'numeric', 'min:0'],
            'filter.vat_rate'   => ['sometimes', 'numeric', 'min:0', 'max:100'],

            // Date filters
            'filter.created_at' => ['sometimes', 'date'],
            'filter.updated_at' => ['sometimes', 'date'],
        ]);
    }

    /**
     * Allowed sort fields.
     *
     * @return array<string>
     */
    protected function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'unit_price', '-unit_price',
            'vat_rate', '-vat_rate',
            'unit', '-unit',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    /**
     * Allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return ['invoiceLines'];
    }

    /**
     * Allowed fields for sparse fieldsets.
     *
     * @return array<string>
     */
    protected function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'description',
            'unit_price',
            'vat_rate',
            'unit',
            'created_at',
            'updated_at',
        ];
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
