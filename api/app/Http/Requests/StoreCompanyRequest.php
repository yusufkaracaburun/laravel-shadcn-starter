<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class StoreCompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:companies'],
            'phone' => ['nullable', 'string', 'max:255'],
            'industry' => ['required', 'string', 'in:technology,finance,healthcare,retail,manufacturing,education'],
            'status' => ['required', 'string', 'in:active,inactive,pending'],
            'employees' => ['required', 'string', 'in:1-10,11-50,51-200,201-500,500+'],
        ];
    }
}
