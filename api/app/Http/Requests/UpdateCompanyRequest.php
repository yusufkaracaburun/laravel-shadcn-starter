<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateCompanyRequest extends FormRequest
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
        /** @var Company $company */
        $company = $this->route('company');
        $companyId = $company?->id ?? null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:companies,email,'.$companyId],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'industry' => ['sometimes', 'required', 'string', 'in:technology,finance,healthcare,retail,manufacturing,education'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive,pending'],
            'employees' => ['sometimes', 'required', 'string', 'in:1-10,11-50,51-200,201-500,500+'],
        ];
    }
}
