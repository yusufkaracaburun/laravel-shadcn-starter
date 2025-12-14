<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $industry
 * @property string $status
 * @property string $employees
 * @property int|null $team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $team
 */
final class Company extends BaseModel
{
    /** @use HasFactory<CompanyFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'industry',
        'status',
        'employees',
        'team_id',
    ];

    /**
     * Get the team that owns the company.
     *
     * @return BelongsTo<Team, covariant $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope a query to only include companies for a specific team.
     *
     * @param  Builder<Company>  $query
     * @return Builder<Company>
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
        return [];
    }
}
