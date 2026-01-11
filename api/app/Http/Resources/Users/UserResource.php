<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

/**
 * @mixin User
 */
final class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,

            'email_verified_at' => $this->formatTimestamp($this->email_verified_at),
            'current_team_id'   => $this->current_team_id,

            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),

            'profile_photo_url' => $this->profile_photo_url,

            'roles' => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($role): array => [
                'id'   => $role->id,
                'name' => $role->name,
            ])->values(),
            ),

            'teams' => $this->when(
                $this->relationLoaded('teams') || $this->relationLoaded('ownedTeams'),
                fn () => TeamResource::collection($this->getAllTeams()),
            ),

            'currentTeam' => $this->whenLoaded('currentTeam', fn (): TeamResource => new TeamResource($this->currentTeam),
            ),
        ];
    }

    private function getAllTeams(): Collection
    {
        if ($this->relationLoaded('ownedTeams') && $this->relationLoaded('teams')) {
            return $this->ownedTeams
                ->merge($this->teams)
                ->unique('id')
                ->values();
        }

        return collect();
    }
}
