<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Base model class providing common functionality for all Eloquent models.
 *
 * This abstract class centralizes common patterns used across models,
 * following Laravel 12 conventions with the casts() method pattern.
 */
abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * By default, all attributes are mass assignable except id, created_at, updated_at.
     * Models can override this with $fillable if they prefer whitelist approach.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['api_key', 'secret', 'password', 'token', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    /**
     * Get the attributes that should be cast.
     *
     * Models should override this method to define their specific casts.
     * This follows Laravel 12's casts() method pattern.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
