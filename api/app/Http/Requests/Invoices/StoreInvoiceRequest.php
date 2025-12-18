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
            'customer_id'    => ['required', 'exists:customers,id'],
            'invoice_number' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number'],
            'date'           => ['required', 'date'],
            'due_days'       => ['required', 'integer', 'min:1', 'max:365'],
            'date_due'       => ['nullable', 'date', 'after_or_equal:date'],
            'status'         => 'required|in:' . implode(',', InvoiceStatus::values()),

            'subtotal'     => ['required', 'numeric', 'min:0'],
            'total_vat_0'  => ['required', 'numeric', 'min:0'],
            'total_vat_9'  => ['required', 'numeric', 'min:0'],
            'total_vat_21' => ['required', 'numeric', 'min:0'],
            'total'        => ['required', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:2000'],

            // Invoice items
            'items'               => ['required', 'array', 'min:1'],
            'items.*.name'        => ['required', 'string', 'max:150'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0.00001'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
            'items.*.vat_rate'    => ['required_with:items', 'numeric', 'min:0', 'max:100'],
            'items.*.sort_order'  => ['nullable', 'integer', 'min:0'],
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
            'customer_id'    => 'customer',
            'invoice_number' => 'invoice number',
            'date'           => 'invoice date',
            'date_due'       => 'due date',
            'due_days'       => 'number of due days',
            'subtotal'       => 'subtotal',
            'total'          => 'total amount',
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
            'status.in'               => 'The status must be one of: ' . implode(', ', InvoiceStatus::values()),
        ]);
    }
}
