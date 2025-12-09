<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Store role request validation.
 *
 * Validates role creation data.
 */
final class StoreRoleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name'),
            ],
            'is_system' => ['boolean'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Role name is required',
            'name.unique' => 'A role with this name already exists',
            'permission_ids.*.exists' => 'One or more permission IDs are invalid',
        ]);
    }
}
