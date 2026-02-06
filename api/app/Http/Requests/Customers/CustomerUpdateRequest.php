<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Models\Customer;
use App\Rules\ValidPhoneNumber;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class CustomerUpdateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Customer|null $customer */
        $customer = $this->route('customer');

        return [
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer),
            ],
            'phone'  => ['nullable', new ValidPhoneNumber()],
            'status' => ['sometimes', 'nullable', 'string', 'in:active,inactive,pending'],
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
