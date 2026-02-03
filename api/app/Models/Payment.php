<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\HasDatesScope;
use App\Observers\PaymentObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PaymentObserver::class])]
final class Payment extends BaseModel
{
    use HasDatesScope;

    public static array $searchable = [
        'payment_number',
        'invoice.invoice_number',
        'customer.name',
        'customer.email',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function casts(): array
    {
        return [
            'status'      => PaymentStatus::class,
            'date'        => 'datetime',
            'paid_at'     => 'datetime',
            'refunded_at' => 'datetime',
            'amount'      => MoneyDecimalCast::class,
        ];
    }
}
