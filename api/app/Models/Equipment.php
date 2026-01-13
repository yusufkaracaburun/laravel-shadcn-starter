<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'serial_number',
        'type',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => EquipmentStatus::class,
    ];
}
