<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class LoginLink extends Model
{
    use HasFactory;
    use MassPrunable;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that the magic link belongs to.
     *
     * @return BelongsTo<User, covariant $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prunable model query.
     * This will delete all magic links that were created more than a week ago.
     */
    public function prunable(): Builder
    {
        return self::query()->where('expires_at', '<=', Date::now())->toBase();
    }
}
