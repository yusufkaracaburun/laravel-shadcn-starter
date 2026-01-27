<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Enums\PaymentStatus;
use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index payment request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for payment listing.
 */
final class IndexPaymentRequest extends BaseIndexFormRequest
{
    /**
     * Custom validation messages specific to payments.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'filter.id.exists'             => 'The selected payment does not exist.',
            'filter.payment_number.string' => 'The payment number must be a valid string.',
            'filter.invoice_id.integer'    => 'The invoice ID must be a number.',
            'filter.invoice_id.exists'     => 'The selected invoice does not exist.',
            'filter.customer_id.integer'   => 'The customer ID must be a number.',
            'filter.customer_id.exists'    => 'The selected customer does not exist.',
            'filter.method.string'         => 'The method must be a valid string.',
            'filter.provider.string'       => 'The provider must be a valid string.',
            'filter.status.in'             => 'The status must be one of: ' . implode(', ', PaymentStatus::values()),
            'filter.date.date'             => 'The date filter must be a valid date.',
            'filter.paid_at.date'          => 'The paid at filter must be a valid date.',
            'filter.between'               => 'The between filter must include a valid start and end date (e.g. 2025-01-01,2025-01-31).',
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
            'filter.id'             => 'payment ID',
            'filter.payment_number' => 'payment number',
            'filter.invoice_id'     => 'invoice ID',
            'filter.customer_id'    => 'customer ID',
            'filter.status'         => 'payment status',
            'filter.date'           => 'payment date',
            'filter.paid_at'        => 'paid at',
            'filter.between'        => 'date range',
            'filter.method'         => 'method',
            'filter.provider'       => 'provider',
        ]);
    }

    /**
     * Filter validation rules specific to payments.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(parent::filterRules(), [
            'filter.payment_number' => ['sometimes', 'string', 'max:50'],
            'filter.invoice_id'     => ['sometimes', 'integer', 'exists:invoices,id'],
            'filter.customer_id'    => ['sometimes', 'integer', 'exists:customers,id'],
            'filter.status'         => ['sometimes', 'in:' . implode(',', PaymentStatus::values())],
            'filter.date'           => ['sometimes', 'date'],
            'filter.paid_at'        => ['sometimes', 'date'],
            'filter.method'         => ['sometimes', 'string'],
            'filter.provider'       => ['sometimes', 'string'],
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
            'payment_number', '-payment_number',
            'invoice_id', '-invoice_id',
            'customer_id', '-customer_id',
            'status', '-status',
            'date', '-date',
            'paid_at', '-paid_at',
            'amount', '-amount',
            'method', '-method',
            'provider', '-provider',
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
        return ['customer', 'invoice'];
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
            'payment_number',
            'invoice_id',
            'customer_id',
            'status',
            'date',
            'paid_at',
            'amount',
            'method',
            'provider',
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
