<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\Cache\CacheInvalidationService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany; // Added for MailTracker
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    public function oauthConnections(): HasMany
    {
        return $this->hasMany(OauthConnection::class);
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_user');
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    public function emails(): MorphMany
    {
        return $this->morphMany(SentEmail::class, 'emailable');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function switchTeam($team): void
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        // Verify user belongs to this team
        throw_if(!$this->teams()->where('teams.id', $teamId)->exists() &&
            !$this->ownedTeams()->where('id', $teamId)->exists(), InvalidArgumentException::class, 'User does not belong to this team');

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

    public function getGuardName(): string
    {
        return 'sanctum';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile-photos')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg']);
    }

    protected function getProfilePhotoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('profile-photos');

        return $url ?: $this->profile_photo_path;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => UserStatus::class,
        ];
    }
}
