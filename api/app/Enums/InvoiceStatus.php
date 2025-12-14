<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * Invoice status enumeration.
 *
 * Represents the various states an invoice can be in.
 */
enum InvoiceStatus: string
{
    use HasEnumsTrait;

    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case OVERDUE = 'overdue';
    case REMINDER = 'reminder';
    case CREDITED = 'credited';
    case PARTIAL_PAID = 'partial_paid';

    /**
     * Get the color associated with this invoice status.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'primary',
            self::SENT => 'info',
            self::PAID => 'success',
            self::UNPAID, self::CANCELLED => 'danger',
            self::CREDITED => 'light',
            self::REFUNDED => 'secondary',
            self::OVERDUE, self::REMINDER, self::PARTIAL_PAID => 'warning',
        };
    }

    /**
     * Get the CSS style class associated with this invoice status.
     */
    public function style(): string
    {
        return match ($this) {
            self::DRAFT => 'badge badge-light-primary',
            self::SENT => 'badge badge-light-info',
            self::PAID => 'badge badge-light-success',
            self::UNPAID, self::CANCELLED => 'badge badge-light-danger',
            self::CREDITED => 'badge badge-light-light',
            self::REFUNDED => 'badge badge-light-secondary',
            self::OVERDUE, self::REMINDER, self::PARTIAL_PAID => 'badge badge-light-warning',
        };
    }

    /**
     * Check if the invoice status is paid.
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if the invoice status is sent.
     */
    public function isSent(): bool
    {
        return $this === self::SENT;
    }

    /**
     * Check if the invoice is unpaid (unpaid, overdue, or partially paid).
     */
    public function isUnpaid(): bool
    {
        return in_array($this, [self::UNPAID, self::OVERDUE, self::PARTIAL_PAID], true);
    }

    /**
     * Check if the invoice is refunded (refunded or credited).
     */
    public function isRefunded(): bool
    {
        return in_array($this, [self::REFUNDED, self::CREDITED], true);
    }
}
