<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string $category
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $progress
 * @property int|null $team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $team
 */
final class Project extends BaseModel
{
    /** @use HasFactory<ProjectFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * Get the team that owns the project.
     *
     * @return BelongsTo<Team, covariant $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope a query to only include projects for a specific team.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    protected function scopeForTeam(Builder $query, ?int $teamId): Builder
    {
        if ($teamId === null) {
            return $query->whereNull('team_id');
        }

        return $query->where('team_id', $teamId);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'progress' => 'integer',
        ];
    }
}
