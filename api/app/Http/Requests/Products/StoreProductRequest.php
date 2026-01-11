<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Store product request validation.
 *
 * Validates product creation data.
 */
final class StoreProductRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Core fields
            'name'        => ['required', 'string', 'max:255', Rule::unique('products', 'name')],
            'description' => ['nullable', 'string'],

            // Price & VAT
            'unit_price' => ['required', 'numeric', 'min:0'],
            'vat_rate'   => ['required', 'numeric', 'min:0', 'max:100'],

            // Unit (optional)
            'unit' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Custom attribute names for clearer validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'name'        => 'product name',
            'description' => 'description',
            'unit_price'  => 'unit price',
            'vat_rate'    => 'VAT rate',
            'unit'        => 'unit',
        ]);
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required'       => 'The product name is required.',
            'name.unique'         => 'An product with this name already exists.',
            'unit_price.required' => 'The unit price is required.',
            'unit_price.numeric'  => 'The unit price must be a valid number.',
            'unit_price.min'      => 'The unit price must be zero or greater.',
            'vat_rate.required'   => 'The VAT rate is required.',
            'vat_rate.numeric'    => 'The VAT rate must be a valid number.',
            'vat_rate.max'        => 'The VAT rate may not be greater than 100%.',
        ]);
    }
}
