<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProjectObserver::class])]
final class Project extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'category',
        'start_date',
        'end_date',
        'progress',
        'team_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function scopeForTeam(Builder $query, ?int $teamId): Builder
    {
        if ($teamId === null) {
            return $query->whereNull('team_id');
        }

        return $query->where('team_id', $teamId);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'progress'   => 'integer',
        ];
    }
}
