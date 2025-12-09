<?php

declare(strict_types=1);

namespace App\Http\Requests\Items;

use App\Models\Item;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Update item request validation.
 *
 * Validates item update data, including unique name validation that ignores the current item.
 */
final class UpdateItemRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Item|null $item */
        $item = $this->route('item');

        return [
            // Core fields
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('items', 'name')->ignore($item),
            ],
            'description' => ['sometimes', 'nullable', 'string'],

            // Price & VAT
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'vat_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],

            // Unit (optional)
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],
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
            'name' => 'item name',
            'description' => 'description',
            'unit_price' => 'unit price',
            'vat_rate' => 'VAT rate',
            'unit' => 'unit',
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
            'name.unique' => 'An item with this name already exists.',
            'unit_price.min' => 'The unit price must be zero or greater.',
            'vat_rate.max' => 'The VAT rate may not be greater than 100%.',
        ]);
    }
}
