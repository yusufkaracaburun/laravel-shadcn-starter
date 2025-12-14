<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Team;
use Illuminate\Http\Request;

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
            'id' => $this->id,
            'name' => $this->name,
            'personal_team' => $this->personal_team,
            'user_id' => $this->user_id,
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }
}
