<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceStatus;
use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index invoice request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for invoice listing.
 */
final class IndexInvoiceRequest extends BaseIndexFormRequest
{
    /**
     * Custom validation messages specific to invoices.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'filter.id.exists'             => 'The selected invoice does not exist.',
            'filter.invoice_number.string' => 'The invoice number must be a valid string.',
            'filter.customer_id.integer'   => 'The customer ID must be a number.',
            'filter.customer_id.exists'    => 'The selected customer does not exist.',
            'filter.status.in'             => 'The status must be one of: ' . implode(', ', InvoiceStatus::values()),
            'filter.date.date'             => 'The date filter must be a valid date.',
            'filter.date_due.date'         => 'The due date filter must be a valid date.',
            'filter.between'               => 'The between filter must include a valid start and end date (e.g. 2025-01-01,2025-01-31).',
            'filter.notes.string'          => 'The notes filter must be a valid string.',
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
            'filter.id'             => 'invoice ID',
            'filter.invoice_number' => 'invoice number',
            'filter.customer_id'    => 'customer ID',
            'filter.status'         => 'invoice status',
            'filter.date'           => 'invoice date',
            'filter.date_due'       => 'due date',
            'filter.between'        => 'date range',
            'filter.subtotal'       => 'subtotal amount',
            'filter.total'          => 'total amount',
            'filter.notes'          => 'notes',
        ]);
    }

    /**
     * Filter validation rules specific to invoices.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(parent::filterRules(), [
            'filter.id'             => ['sometimes', 'integer', 'exists:invoices,id'],
            'filter.invoice_number' => ['sometimes', 'string', 'max:50'],
            'filter.customer_id'    => ['sometimes', 'integer', 'exists:customers,id'],
            'filter.status'         => ['sometimes', 'in:' . implode(',', InvoiceStatus::values())],
            'filter.date'           => ['sometimes', 'date'],
            'filter.date_due'       => ['sometimes', 'date'],
            'filter.between'        => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}-\d{2},\d{4}-\d{2}-\d{2}$/'],
            'filter.subtotal'       => ['sometimes', 'numeric', 'min:0'],
            'filter.total'          => ['sometimes', 'numeric', 'min:0'],
            'filter.notes'          => ['sometimes', 'string', 'max:1000'],
            'filter.search'         => ['sometimes', 'string', 'max:255'],
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
            'invoice_number', '-invoice_number',
            'customer_id', '-customer_id',
            'status', '-status',
            'date', '-date',
            'date_due', '-date_due',
            'subtotal', '-subtotal',
            'total', '-total',
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
        return [
            'customer',
        ];
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
            'invoice_number',
            'customer_id',
            'status',
            'date',
            'date_due',
            'subtotal',
            'total_vat_0',
            'total_vat_9',
            'total_vat_21',
            'total',
            'notes',
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
