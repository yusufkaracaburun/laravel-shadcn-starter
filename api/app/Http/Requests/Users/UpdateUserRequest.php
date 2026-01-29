<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $userId = $user?->id ?? null;

        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'email'         => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'      => ['sometimes', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['sometimes', 'image', 'max:2048'], // Max 2MB
            'role'          => ['sometimes', 'nullable', 'string', 'exists:roles,name'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'name'                  => 'name',
                'email'                 => 'email address',
                'password'              => 'password',
                'password_confirmation' => 'password confirmation',
                'profile_photo'         => 'profile photo',
                'role'                  => 'role',
            ],
        );
    }
}
