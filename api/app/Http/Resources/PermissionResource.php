<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\Users\UserResource;

/**
 * @mixin Permission
 */
final class PermissionResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'guard_name' => $this->guard_name,
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
            'roles'      => RoleResource::collection($this->whenLoaded('roles')),
            'users'      => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
