<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Update role request validation.
 *
 * Validates role update data, including unique name validation that ignores the current role.
 */
final class UpdateRoleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Role|null $role */
        $role = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role),
            ],
            'permission_ids'   => ['nullable', 'array'],
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
            'name.required'           => 'Role name is required',
            'name.unique'             => 'A role with this name already exists',
            'permission_ids.*.exists' => 'One or more permission IDs are invalid',
        ]);
    }
}
