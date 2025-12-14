<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMoneyTrait;
use App\Observers\InvoiceItemObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * Invoice item model.
 *
 * Represents a line item on an invoice.
 */
#[ObservedBy([InvoiceItemObserver::class])]
final class InvoiceItem extends BaseModel
{
    use HasMoneyTrait;

    /**
     * Get the invoice this item belongs to.
     *
     * @return BelongsTo<Invoice, covariant $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the item this invoice item is based on (if linked to an item).
     *
     * @return BelongsTo<Item, covariant $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        self::saving(function (InvoiceItem $item): void {
            $item->calculateItemTotals();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:5',
            'unit_price' => MoneyDecimalCast::class,
            'vat_rate' => 'decimal:0',
            'total_excl_vat' => MoneyDecimalCast::class,
            'total_vat' => MoneyDecimalCast::class,
            'total_incl_vat' => MoneyDecimalCast::class,
        ];
    }
}
