<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Base form request class providing common functionality for all form requests.
 *
 * This class serves as a foundation for all form requests in the application,
 * providing default implementations for authorization and validation methods.
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * By default, authorize all requests. Override in child class if needed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Override in child class to provide specific validation rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors.
     *
     * Override in child class to provide custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * Override in child class to provide custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     *
     * Override in child class to prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        // Override in child class if needed
    }
}
