<?php

declare(strict_types=1);

namespace App\Http\Resources\Projects;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

/**
 * @mixin Project
 */
final class ProjectResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'category'    => $this->category,
            'start_date'  => $this->formatDate($this->start_date),
            'end_date'    => $this->formatDate($this->end_date),
            'progress'    => $this->progress,
            'team_id'     => $this->team_id,
            'created_at'  => $this->formatTimestamp($this->created_at),
            'updated_at'  => $this->formatTimestamp($this->updated_at),
        ];
    }
}
