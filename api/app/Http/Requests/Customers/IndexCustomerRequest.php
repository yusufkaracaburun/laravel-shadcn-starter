<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Http\Requests\BaseIndexFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Index customer request validation.
 *
 * Validates pagination, filtering, sorting, includes, and fields for customer listing.
 */
final class IndexCustomerRequest extends BaseIndexFormRequest
{
    /**
     * Custom validation messages specific to customers.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'filter.id.exists'       => 'The selected customer does not exist.',
                'filter.name.string'     => 'The name filter must be a valid string.',
                'filter.email.email'     => 'The email filter must be a valid email address.',
                'filter.phone.string'    => 'The phone filter must be a valid string.',
                'filter.city.string'     => 'The city filter must be a valid string.',
                'filter.country.string'  => 'The country filter must be a valid string.',
                'filter.type.in'         => 'The customer type must be either "private" or "business".',
                'filter.status.in'       => 'The status must be either "active", "inactive", or "pending".',
                'filter.created_at.date' => 'The created_at filter must be a valid date.',
            ],
        );
    }

    /**
     * Custom attribute names for better error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'filter.type'        => 'customer type',
                'filter.name'        => 'name',
                'filter.email'       => 'email address',
                'filter.phone'       => 'phone number',
                'filter.city'        => 'city',
                'filter.country'     => 'country',
                'filter.kvk_number'  => 'Chamber of Commerce number',
                'filter.vat_number'  => 'VAT number',
                'filter.iban_number' => 'IBAN number',
                'filter.status'      => 'status',
                'filter.created_at'  => 'creation date',
            ],
        );
    }

    /**
     * Filter validation rules specific to customers.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return array_merge(
            parent::filterRules(),
            [
                'filter.type'        => 'sometimes|in:private,business',
                'filter.name'        => 'sometimes|string|max:255',
                'filter.email'       => 'sometimes|email|max:255',
                'filter.phone'       => 'sometimes|string|max:255',
                'filter.status'      => 'sometimes|in:active,inactive,pending',
                'filter.city'        => 'sometimes|string|max:255',
                'filter.country'     => 'sometimes|string|max:255',
                'filter.kvk_number'  => 'sometimes|string|max:50',
                'filter.vat_number'  => 'sometimes|string|max:50',
                'filter.iban_number' => 'sometimes|string|max:50',
                'filter.created_at'  => 'sometimes|date',
            ],
        );
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
            'type', '-type',
            'name', '-name',
            'address', '-address',
            'zipcode', '-zipcode',
            'city', '-city',
            'country', '-country',
            'email', '-email',
            'phone', '-phone',
            'status', '-status',
            'kvk_number', '-kvk_number',
            'vat_number', '-vat_number',
            'iban_number', '-iban_number',
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
        return ['contacts', 'primaryContact', 'invoices'];
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
            'type',
            'name',
            'address',
            'zipcode',
            'city',
            'country',
            'email',
            'phone',
            'status',
            'kvk_number',
            'vat_number',
            'iban_number',
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
