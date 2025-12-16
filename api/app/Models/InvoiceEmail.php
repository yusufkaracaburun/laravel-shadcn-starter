<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceEmail model.
 *
 * Represents an email sent for an invoice.
 */
final class InvoiceEmail extends BaseModel
{
    /**
     * Get the invoice this email belongs to.
     *
     * @return BelongsTo<Invoice, covariant $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EmailStatus::class,
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }
}
