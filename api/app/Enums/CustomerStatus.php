<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * User status enumeration.
 *
 * Represents the various states a user can be in within the system.
 */
enum CustomerStatus: string
{
    use HasEnumsTrait;

    case REGISTERED = 'registered';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get the color associated with this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::REGISTERED => 'warning',
            self::ACTIVE     => 'success',
            self::INACTIVE   => 'danger',
        };
    }

    /**
     * Get the CSS style class associated with this status.
     */
    public function style(): string
    {
        return match ($this) {
            self::REGISTERED => 'badge badge-light-warning',
            self::ACTIVE     => 'badge badge-light-success',
            self::INACTIVE   => 'badge badge-light-danger',
        };
    }

    /**
     * Check if the status is registered.
     */
    public function isRegistered(): bool
    {
        return $this === self::REGISTERED;
    }

    /**
     * Check if the status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the status is inactive.
     *
     * Includes INACTIVE, PENDING, SUSPENDED, and REGISTERED statuses.
     */
    public function isInactive(): bool
    {
        return in_array($this, [self::INACTIVE, self::REGISTERED], true);
    }
}
