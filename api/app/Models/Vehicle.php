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

    /**
     * Searchable fields for this model.
     *
     * @var list<string>
     */
    public static array $searchable = [
        'make',
        'model',
        'license_plate',
        'vin',
        'drivers.name',
        'drivers.email',
    ];

    /**
     * Get the drivers (users) associated with the vehicle.
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vehicle_user');
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VehicleStatus::class,
            'year'   => 'integer',
        ];
    }

    /**
     * Scope a query to only include active vehicles.
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', VehicleStatus::ACTIVE);
    }
}
