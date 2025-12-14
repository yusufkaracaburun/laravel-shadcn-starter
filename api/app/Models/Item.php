<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ItemObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ItemObserver::class])]
final class Item extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => MoneyDecimalCast::class,
        'vat_rate' => 'decimal:2',
    ];

    /**
     * Get all invoice items that use this item.
     *
     * @return HasMany<InvoiceItem>
     */
    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
