<?php

declare(strict_types=1);

namespace App\Http\Resources\Roles;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Permissions\PermissionResource;

/**
 * @mixin Role
 */
final class RoleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'is_system'         => $this->is_system,
            'users'             => UserResource::collection($this->whenLoaded('users')),
            'permissions'       => PermissionResource::collection($this->whenLoaded('permissions')),
            'users_count'       => $this->whenCounted('users'),
            'permissions_count' => $this->whenCounted('permissions'),
        ];
    }
}
