<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMoneyTrait;
use App\Observers\InvoiceItemObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * InvoiceItem model.
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
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Scope a query to order items by sort_order.
     *
     * @param  Builder<InvoiceItem>  $query
     * @return Builder<InvoiceItem>
     */
    #[Scope]
    protected function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
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
            'vat_rate' => 'decimal:2',
            'total_excl_vat' => MoneyDecimalCast::class,
            'total_vat' => MoneyDecimalCast::class,
            'total_incl_vat' => MoneyDecimalCast::class,
            'sort_order' => 'integer',
        ];
    }
}
