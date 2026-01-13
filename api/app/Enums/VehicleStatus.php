<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

enum VehicleStatus: string
{
    use HasEnumsTrait;

    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case INACTIVE = 'inactive';

    /**
     * Get the color associated with this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::MAINTENANCE => 'warning',
            self::INACTIVE => 'danger',
        };
    }

    /**
     * Get the CSS style class associated with this status.
     */
    public function style(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge badge-light-success',
            self::MAINTENANCE => 'badge badge-light-warning',
            self::INACTIVE => 'badge badge-light-danger',
        };
    }

    /**
     * Check if the status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the status is maintenance.
     */
    public function isMaintenance(): bool
    {
        return $this === self::MAINTENANCE;
    }

    /**
     * Check if the status is inactive.
     */
    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }
}
