<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCacheKeys;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class BaseModel extends Model
{
    use HasCacheKeys;
    use HasFactory;

    protected $guarded = [];

    protected $hidden = ['api_key', 'secret', 'password', 'token', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected function casts(): array
    {
        return [];
    }
}
