<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\PermissionObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\Permission\Models\Permission as SpatiePermission;

#[ObservedBy([PermissionObserver::class])]
final class Permission extends SpatiePermission
{
    use HasFactory;
}
