<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Enums\CustomerStatus;
use App\Rules\ValidPhoneNumber;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Customer store request validation.
 *
 * Validates customer creation data.
 */
final class CustomerStoreRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'string', 'email', 'max:255', 'unique:customers'],
            'phone'  => ['nullable', new ValidPhoneNumber()],
            'status' => ['required', Rule::enum(CustomerStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'name'   => 'name',
            'email'  => 'email address',
            'phone'  => 'phone number',
            'status' => 'status',
        ]);
    }
}
