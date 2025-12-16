<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * Email status enumeration.
 *
 * Represents the various states an email can be in.
 */
enum EmailStatus: string
{
    use HasEnumsTrait;

    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';

    /**
     * Get the color associated with this email status.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::SENT => 'success',
            self::FAILED => 'danger',
        };
    }

    /**
     * Get the CSS style class associated with this email status.
     */
    public function style(): string
    {
        return match ($this) {
            self::PENDING => 'badge badge-light-warning',
            self::SENT => 'badge badge-light-success',
            self::FAILED => 'badge badge-light-danger',
        };
    }

    /**
     * Get the translated label for this email status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
        };
    }
}
