<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

enum EquipmentStatus: string
{
    use HasEnumsTrait;

    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case INACTIVE = 'inactive';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE      => 'success',
            self::MAINTENANCE => 'warning',
            self::INACTIVE    => 'danger',
        };
    }

    public function style(): string
    {
        return match ($this) {
            self::ACTIVE      => 'badge badge-light-success',
            self::MAINTENANCE => 'badge badge-light-warning',
            self::INACTIVE    => 'badge badge-light-danger',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isMaintenance(): bool
    {
        return $this === self::MAINTENANCE;
    }

    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }
}
