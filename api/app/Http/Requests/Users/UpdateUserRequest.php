<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateUserRequest extends FormRequest
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
}
