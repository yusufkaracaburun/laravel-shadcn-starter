<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LoginLink extends BaseModel
{
    use MassPrunable;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }
}
