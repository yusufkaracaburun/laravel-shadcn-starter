<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Update permission request validation.
 *
 * Validates permission update data, including unique name validation that ignores the current permission.
 */
final class UpdatePermissionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Permission|null $permission */
        $permission = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission),
            ],
            'guard_name' => ['sometimes', 'nullable', 'string', 'max:255'],
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
