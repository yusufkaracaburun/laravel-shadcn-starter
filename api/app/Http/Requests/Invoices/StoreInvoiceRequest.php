<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Store invoice request validation.
 *
 * Validates invoice creation data.
 */
final class StoreInvoiceRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_number' => ['nullable', 'string', 'max:50', 'unique:invoices,invoice_number'],
            'date' => ['required', 'date'],
            'due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'date_due' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => 'required|in:'.implode(',', InvoiceStatus::values()),

            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'total_vat_0' => ['nullable', 'numeric', 'min:0'],
            'total_vat_9' => ['nullable', 'numeric', 'min:0'],
            'total_vat_21' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom attribute names for nicer validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'customer_id' => 'customer',
            'invoice_number' => 'invoice number',
            'date' => 'invoice date',
            'date_due' => 'due date',
            'due_days' => 'number of due days',
            'subtotal' => 'subtotal',
            'total' => 'total amount',
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
            'date_due.after_or_equal' => 'The due date must be after or equal to the invoice date.',
            'status.in' => 'The status must be one of: '.implode(', ', InvoiceStatus::values()),
        ]);
    }
}
