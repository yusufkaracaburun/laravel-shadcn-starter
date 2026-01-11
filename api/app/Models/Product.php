<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ProductObserver;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProductObserver::class])]
final class Product extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => MoneyDecimalCast::class,
        'vat_rate'   => 'decimal:0',
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
