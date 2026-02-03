<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasDatesScope;
use App\Traits\HasMoneyTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Observers\InvoiceObserver;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasInvoiceNumberTrait;
use Cknow\Money\Casts\MoneyDecimalCast;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([InvoiceObserver::class])]
final class Invoice extends BaseModel
{
    use HasDatesScope;
    use HasInvoiceNumberTrait;
    use HasMoneyTrait;
    use LogsActivity;

    public static array $searchable = [
        'invoice_number',
        'notes',
        'customer.name',
        'customer.email',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => "Invoice has been {$eventName}")
            ->dontLogIfAttributesChangedOnly(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function emails(): MorphMany
    {
        return $this->morphMany(SentEmail::class, 'emailable')->orderBy('created_at', 'desc');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject')->orderBy('created_at', 'desc');
    }

    public function generatePdf()
    {
        return Pdf::loadView('pdf.invoice', ['invoice' => $this]);
    }

    protected static function booted(): void
    {
        self::creating(function (Invoice $invoice): void {
            $invoice->generateInvoiceNumber();
        });

        self::saving(function (Invoice $invoice): void {
            $invoice->calculateInvoiceTotals();
        });
    }

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'date_due'     => 'date',
            'status'       => InvoiceStatus::class,
            'subtotal'     => MoneyDecimalCast::class,
            'total_vat_0'  => MoneyDecimalCast::class,
            'total_vat_9'  => MoneyDecimalCast::class,
            'total_vat_21' => MoneyDecimalCast::class,
            'total'        => MoneyDecimalCast::class,
        ];
    }

    #[Scope]
    protected function draft(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::PAID);
    }
}
