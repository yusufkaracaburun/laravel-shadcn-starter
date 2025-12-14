<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * Payment status enumeration.
 *
 * Represents the various states a payment can be in.
 */
enum PaymentStatus: string
{
    use HasEnumsTrait;

    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    /**
     * Get the color associated with this payment status.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
            self::CANCELLED => 'secondary',
        };
    }

    /**
     * Get the CSS style class associated with this payment status.
     */
    public function style(): string
    {
        return match ($this) {
            self::PENDING => 'badge badge-light-warning',
            self::PAID => 'badge badge-light-success',
            self::FAILED => 'badge badge-light-danger',
            self::REFUNDED => 'badge badge-light-info',
            self::CANCELLED => 'badge badge-light-secondary',
        };
    }

    /**
     * Check if the payment status is pending.
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the payment status is paid.
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if the payment status is refunded.
     */
    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }

    /**
     * Check if the payment is completed (paid or refunded).
     */
    public function isCompleted(): bool
    {
        return in_array($this, [self::PAID, self::REFUNDED], true);
    }
}
