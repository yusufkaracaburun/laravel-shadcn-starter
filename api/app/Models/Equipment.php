<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EquipmentStatus;
use App\Observers\EquipmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([EquipmentObserver::class])]
final class Equipment extends BaseModel
{
    /**
     * Cache keys for equipment endpoints.
     *
     * @var array<string, string>
     */
    public const CACHE = [
        'index' => 'api.equipments.index',
        'show'  => 'api.equipments.show',
    ];

    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'serial_number',
        'type',
        'status',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
        ];
    }
}
