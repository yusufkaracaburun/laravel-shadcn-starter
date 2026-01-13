<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Vehicle extends BaseModel
{
    use HasFactory;

    public static array $searchable = [
        'make',
        'model',
        'license_plate',
        'vin',
        'drivers.name',
        'drivers.email',
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vehicle_user');
    }

    protected function casts(): array
    {
        return [
            'status' => VehicleStatus::class,
            'year'   => 'integer',
        ];
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', VehicleStatus::ACTIVE);
    }
}
