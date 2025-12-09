<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Store permission request validation.
 *
 * Validates permission creation data.
 */
final class StorePermissionRequest extends BaseFormRequest
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
                Rule::unique('permissions', 'name'),
            ],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
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
            'name.required' => 'The permission name is required.',
            'name.unique' => 'A permission with this name already exists.',
            'role_ids.array' => 'Role IDs must be an array.',
            'role_ids.*.exists' => 'One or more selected roles do not exist.',
        ]);
    }
}
