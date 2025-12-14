<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Enums\PaymentStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Store payment request validation.
 *
 * Validates payment creation data.
 */
final class StorePaymentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Foreign keys
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],

            // Payment details
            'payment_number' => ['required', 'string', 'max:50', Rule::unique('payments', 'payment_number')],
            'amount' => ['required', 'numeric', 'min:0.01'],

            // Method & provider
            'method' => ['nullable', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:50'],
            'provider_reference' => ['nullable', 'string', 'max:100'],

            // Enum status
            'status' => ['required', Rule::in(PaymentStatus::values())],

            // Date fields
            'date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'refunded_at' => ['nullable', 'date'],
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
            'invoice_id' => 'invoice',
            'customer_id' => 'customer',
            'payment_number' => 'payment number',
            'amount' => 'amount',
            'method' => 'payment method',
            'provider' => 'provider',
            'provider_reference' => 'reference',
            'status' => 'status',
            'date' => 'booking date',
            'paid_at' => 'payment date',
            'refunded_at' => 'refund date',
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
            'payment_number.unique' => 'This payment number already exists.',
            'amount.min' => 'The amount must be greater than 0.',
            'date.required' => 'A booking date is required.',
            'invoice_id.exists' => 'The specified invoice does not exist.',
            'customer_id.exists' => 'The specified customer does not exist.',
        ]);
    }
}
