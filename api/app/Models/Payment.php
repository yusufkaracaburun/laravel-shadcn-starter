<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\HasDatesScope;
use App\Observers\PaymentObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * Payment model.
 *
 * Represents a payment made by a customer, optionally linked to an invoice.
 */
#[ObservedBy([PaymentObserver::class])]
final class Payment extends BaseModel
{
    use HasDatesScope;

    /**
     * Searchable fields for this model.
     *
     * @var list<string>
     */
    public static array $searchable = [
        'payment_number',
        'invoice.invoice_number',
        'customer.name',
        'customer.email',
    ];

    /**
     * Get the invoice this payment belongs to.
     *
     * @return BelongsTo<Invoice, covariant $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer this payment belongs to.
     *
     * @return BelongsTo<Customer, covariant $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'date' => 'datetime',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'amount' => MoneyDecimalCast::class,
        ];
    }
}
