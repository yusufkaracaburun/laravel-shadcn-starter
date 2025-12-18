<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Enums\PaymentStatus;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Update payment request validation.
 *
 * Validates payment update data, including unique payment number validation that ignores the current payment.
 */
final class UpdatePaymentRequest extends BaseFormRequest
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
            'invoice_id'  => ['sometimes', 'integer', 'exists:invoices,id'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],

            // Payment details
            'payment_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('payments', 'payment_number')->ignore($this->route('payment')),
            ],
            'amount' => ['sometimes', 'numeric', 'min:0'],

            // Method & provider
            'method'             => ['sometimes', 'string', 'nullable', 'max:50'],
            'provider'           => ['sometimes', 'string', 'nullable', 'max:50'],
            'provider_reference' => ['sometimes', 'string', 'nullable', 'max:100'],

            // Enum status
            'status' => ['sometimes', Rule::in(PaymentStatus::values())],

            // Date fields
            'date'        => ['sometimes', 'date'],
            'paid_at'     => ['sometimes', 'nullable', 'date'],
            'refunded_at' => ['sometimes', 'nullable', 'date'],
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
            'invoice_id'         => 'invoice',
            'customer_id'        => 'customer',
            'payment_number'     => 'payment number',
            'amount'             => 'amount',
            'method'             => 'payment method',
            'provider'           => 'provider',
            'provider_reference' => 'reference',
            'status'             => 'status',
            'date'               => 'booking date',
            'paid_at'            => 'payment date',
            'refunded_at'        => 'refund date',
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
            'payment_number.unique' => 'This payment number already exists.',
            'amount.min'            => 'The amount must be greater than 0.',
        ];
    }
}
