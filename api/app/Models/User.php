<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Cache\CacheInvalidationService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;

    use Notifiable;

    use HasRoles;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the Oauth Connections for the user.
     *
     * @return HasMany<OauthConnection, covariant $this>
     */
    public function oauthConnections(): HasMany
    {
        return $this->hasMany(OauthConnection::class);
    }

    /**
     * Get the current team for the user.
     *
     * @return BelongsTo<Team, covariant $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Get all teams the user belongs to.
     *
     * @return BelongsToMany<Team>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get teams owned by the user.
     *
     * @return HasMany<Team>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    /**
     * Switch the user's current team.
     *
     * @param  Team|int  $team
     */
    public function switchTeam($team): void
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        // Verify user belongs to this team
        throw_if(! $this->teams()->where('teams.id', $teamId)->exists() &&
            ! $this->ownedTeams()->where('id', $teamId)->exists(), InvalidArgumentException::class, 'User does not belong to this team');

        $oldTeamId = $this->current_team_id;
        $this->update(['current_team_id' => $teamId]);
        $this->refresh();

        // Invalidate caches for both old and new team
        if ($oldTeamId !== null) {
            CacheInvalidationService::onTeamSwitched($this->id, $oldTeamId, $teamId);
        } else {
            CacheInvalidationService::invalidateUser($this->id);
            CacheInvalidationService::invalidateTeam($teamId);
        }
    }

    /**
     * Get the guard name for Spatie Permission.
     *
     * This ensures permissions are checked using the 'sanctum' guard
     * which is appropriate for API authentication.
     */
    public function getGuardName(): string
    {
        return 'sanctum';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
