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

    /**
     * Searchable fields for this model.
     *
     * @var list<string>
     */
    public static array $searchable = [
        'name',
        'serial_number',
        'type',
        'status',
    ];

    protected $table = 'equipments';

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
