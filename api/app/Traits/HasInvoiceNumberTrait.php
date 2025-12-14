<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Invoice;

/**
 * Trait for generating invoice numbers.
 *
 * Provides automatic invoice number generation based on year and sequence.
 */
trait HasInvoiceNumberTrait
{
    /**
     * Generate a new invoice number if one doesn't exist.
     *
     * Format: INV-YYYY-NNNNNN (e.g., INV-2025-000001)
     *
     * @param  string  $prefix  Prefix for invoice number (default: 'INV')
     */
    public function generateInvoiceNumber(string $prefix = 'INV'): void
    {
        if (! $this instanceof Invoice) {
            return;
        }

        if (! $this->invoice_number) {
            $year = now()->year;

            $lastNumber = self::whereYear('date', $year)
                ->lockForUpdate()
                ->latest('date')
                ->value('invoice_number');

            $next = 1;

            if ($lastNumber && preg_match("/{$prefix}-{$year}-(\d+)/", (string) $lastNumber, $matches)) {
                $next = (int) $matches[1] + 1;
            }

            $this->invoice_number = sprintf('%s-%d-%06d', $prefix, $year, $next);
        }
    }
}
