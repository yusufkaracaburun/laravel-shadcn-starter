<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
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
     * Configure the panel access.
     * In production, you should modify this to check for admin permissions.
     *
     * @see https://docs.larasonic.com/features/admin
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // In local environment, allow all users
        // In production, you should check for admin permissions:
        // return $this->is_admin ?? false;
        return true;
    }

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
        if (! $this->teams()->where('teams.id', $teamId)->exists() &&
            ! $this->ownedTeams()->where('id', $teamId)->exists()) {
            throw new InvalidArgumentException('User does not belong to this team');
        }

        $this->update(['current_team_id' => $teamId]);
        $this->refresh();
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
