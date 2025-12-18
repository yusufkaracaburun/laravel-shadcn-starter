<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\RoleObserver;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([RoleObserver::class])]
final class Role extends SpatieRole
{
    use HasFactory;

    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'is_system'  => 'boolean',
    ];
}
