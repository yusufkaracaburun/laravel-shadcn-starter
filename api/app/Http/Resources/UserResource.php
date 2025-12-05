<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User API Resource
 *
 * Transforms User model into a consistent API response format.
 * Includes teams and current team relationships.
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'current_team_id' => $this->current_team_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'profile_photo_url' => $this->profile_photo_url,
            'roles' => $this->when(
                $this->relationLoaded('roles'),
                fn () => $this->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ])->values()
            ),
            'teams' => TeamResource::collection($this->when(
                $this->relationLoaded('teams') || $this->relationLoaded('ownedTeams'),
                fn () => $this->getAllTeams()
            )),
            'currentTeam' => new TeamResource($this->whenLoaded('currentTeam')),
        ];
    }

    /**
     * Get all teams (owned + member) for the user.
     *
     * @return Collection
     */
    private function getAllTeams()
    {
        if ($this->relationLoaded('ownedTeams') && $this->relationLoaded('teams')) {
            return $this->ownedTeams->merge($this->teams)->unique('id')->values();
        }

        return collect();
    }
}
