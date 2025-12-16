<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasDatesScope;
use App\Traits\HasMoneyTrait;
use App\Observers\InvoiceObserver;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasInvoiceNumberTrait;
use Cknow\Money\Casts\MoneyDecimalCast;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * Invoice model.
 *
 * Represents an invoice sent to a customer.
 */
#[ObservedBy([InvoiceObserver::class])]
final class Invoice extends BaseModel
{
    use HasDatesScope;
    use HasInvoiceNumberTrait;
    use HasMoneyTrait;
    use LogsActivity;

    /**
     * Searchable fields for this model.
     *
     * @var list<string>
     */
    public static array $searchable = [
        'invoice_number',
        'notes',
        'customer.name',
        'customer.email',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the customer this invoice belongs to.
     *
     * @return BelongsTo<Customer, covariant $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get all items for this invoice.
     *
     * @return HasMany<InvoiceItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all payments for this invoice.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all emails sent for this invoice.
     *
     * @return HasMany<InvoiceEmail>
     */
    public function emails(): HasMany
    {
        return $this->hasMany(InvoiceEmail::class);
    }

    /**
     * Get all activity logs for this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        self::creating(function (Invoice $invoice): void {
            $invoice->generateInvoiceNumber();
        });

        self::saving(function (Invoice $invoice): void {
            $invoice->calculateInvoiceTotals();
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
            'date' => 'date',
            'date_due' => 'date',
            'status' => InvoiceStatus::class,
            'subtotal' => MoneyDecimalCast::class,
            'total_vat_0' => MoneyDecimalCast::class,
            'total_vat_9' => MoneyDecimalCast::class,
            'total_vat_21' => MoneyDecimalCast::class,
            'total' => MoneyDecimalCast::class,
        ];
    }

    /**
     * Scope a query to only include draft invoices.
     */
    #[Scope]
    protected function draft(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    /**
     * Scope a query to only include paid invoices.
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::PAID);
    }
}
