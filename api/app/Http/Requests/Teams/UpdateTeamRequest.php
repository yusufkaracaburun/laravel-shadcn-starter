<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Super admin bypasses authorization in form request
        $user = Auth::user();
        if ($user instanceof User) {
            $permissionRegistrar = resolve(PermissionRegistrar::class);
            $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId(null);
            $permissionRegistrar->forgetCachedPermissions();

            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }

            $user->unsetRelation('roles');
            $isSuperAdmin = $user->hasRole('super-admin');

            $permissionRegistrar->setPermissionsTeamId($originalTeamId);

            if ($isSuperAdmin) {
                return true;
            }
        }

        // For regular users, authorization is checked in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $this->route('team');

        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'personal_team' => ['sometimes', 'boolean'],
        ];
    }
}
