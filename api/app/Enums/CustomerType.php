<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * Customer type enumeration.
 *
 * Represents whether a customer is a business or private individual.
 */
enum CustomerType: string
{
    use HasEnumsTrait;

    case BUSINESS = 'business';
    case PRIVATE = 'private';

    /**
     * Get the color associated with this customer type.
     */
    public function color(): string
    {
        return match ($this) {
            self::BUSINESS => 'primary',
            self::PRIVATE => 'secondary',
        };
    }

    /**
     * Get the CSS style class associated with this customer type.
     */
    public function style(): string
    {
        return match ($this) {
            self::BUSINESS => 'badge badge-light-primary',
            self::PRIVATE => 'badge badge-light-secondary',
        };
    }

    /**
     * Check if the customer type is business.
     */
    public function isBusiness(): bool
    {
        return $this === self::BUSINESS;
    }

    /**
     * Check if the customer type is private.
     */
    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }
}
