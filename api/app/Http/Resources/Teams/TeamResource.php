<?php

declare(strict_types=1);

namespace App\Http\Resources\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Users\UserResource;

/**
 * @mixin Team
 */
final class TeamResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'personal_team' => (bool) $this->personal_team,
            'user_id'       => $this->user_id,
            'users'         => UserResource::collection($this->whenLoaded('users')),
            'users_count'   => $this->whenCounted('users'),
            'created_at'    => $this->formatTimestamp($this->created_at),
            'updated_at'    => $this->formatTimestamp($this->updated_at),
        ];
    }
}
