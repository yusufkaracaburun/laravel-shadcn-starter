<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @mixin Activity
 */
final class ActivityResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        $properties = $this->properties ?? [];
        $oldValues = $properties['old'] ?? [];
        $newValues = $properties['attributes'] ?? [];

        return [
            'id'           => $this->id,
            'log_name'     => $this->log_name,
            'description'  => $this->description,
            'subject_id'   => $this->subject_id,
            'subject_type' => $this->subject_type,
            'causer_id'    => $this->causer_id,
            'causer_type'  => $this->causer_type,
            'causer'       => $this->whenLoaded('causer', fn (): ?array => $this->causer ? [
                'id'    => $this->causer->id,
                'name'  => $this->causer->name ?? $this->causer->email ?? 'System',
                'email' => $this->causer->email ?? null,
            ] : null),
            'properties' => [
                'old'        => $oldValues,
                'attributes' => $newValues,
            ],
            'event'      => $this->event,
            'batch_uuid' => $this->batch_uuid ?? null,
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
