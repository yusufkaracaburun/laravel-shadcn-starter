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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return self::query()->where('expires_at', '<=', Date::now())->toBase();
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }
}
