<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Update invoice request validation.
 *
 * Validates invoice update data, including unique invoice number validation that ignores the current invoice.
 */
final class UpdateInvoiceRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'exists:customers,id'],
            'invoice_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')->ignore($this->route('invoice')),
            ],
            'date' => ['sometimes', 'date'],
            'due_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'date_due' => ['sometimes', 'date', 'after_or_equal:date'],
            'status' => ['sometimes', Rule::in(InvoiceStatus::values())],

            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'total_vat_0' => ['sometimes', 'numeric', 'min:0'],
            'total_vat_9' => ['sometimes', 'numeric', 'min:0'],
            'total_vat_21' => ['sometimes', 'numeric', 'min:0'],
            'total' => ['sometimes', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom attribute names for better validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'invoice_number' => 'invoice number',
            'date' => 'invoice date',
            'date_due' => 'due date',
            'due_days' => 'number of due days',
            'subtotal' => 'subtotal',
            'total' => 'total amount',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_due.after_or_equal' => 'The due date must be after or equal to the invoice date.',
            'status.in' => 'The status must be one of: '.implode(', ', InvoiceStatus::values()),
        ];
    }
}
