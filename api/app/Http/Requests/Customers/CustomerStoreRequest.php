<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Customer store request validation.
 *
 * Validates customer creation data.
 */
final class CustomerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
            'status' => ['nullable', 'string', 'in:active,inactive,pending'],
        ];
    }
}
