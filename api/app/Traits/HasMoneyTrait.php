<?php

declare(strict_types=1);

namespace App\Traits;

use Cknow\Money\Money;
use App\Models\Invoice;
use App\Models\InvoiceItem;

/**
 * Trait for calculating money totals for invoices and invoice items.
 *
 * Provides methods for calculating subtotals, VAT amounts, and totals.
 */
trait HasMoneyTrait
{
    /**
     * Calculate totals for an InvoiceItem (line item).
     */
    public function calculateItemTotals(): void
    {
        if (! $this instanceof InvoiceItem) {
            return;
        }

        $unitPrice = $this->unit_price; // Money via cast
        $quantity = (string) $this->quantity;

        $subtotal = $unitPrice->multiply($quantity);

        $vatRate = (string) $this->vat_rate; // e.g., 21.00000
        $vatAmount = $subtotal->multiply(bcdiv($vatRate, '100', 10));

        $totalIncl = $subtotal->add($vatAmount);

        $this->total_excl_vat = $subtotal;
        $this->total_vat = $vatAmount;
        $this->total_incl_vat = $totalIncl;
    }

    /**
     * Calculate totals for an Invoice (document).
     */
    public function calculateInvoiceTotals(): void
    {
        if (! $this instanceof Invoice) {
            return;
        }

        $subtotal = Money::EUR(0);
        $vat0 = Money::EUR(0);
        $vat9 = Money::EUR(0);
        $vat21 = Money::EUR(0);
        $total = Money::EUR(0);

        foreach ($this->items as $item) {
            $subtotal = $subtotal->add($item->total_excl_vat);

            // Distribute VAT by rate
            $rate = (float) $item->vat_rate;

            if ($rate === 0) {
                $vat0 = $vat0->add($item->total_vat);
            } elseif ($rate === 9) {
                $vat9 = $vat9->add($item->total_vat);
            } elseif ($rate === 21) {
                $vat21 = $vat21->add($item->total_vat);
            }

            $total = $total->add($item->total_incl_vat);
        }

        $this->subtotal = $subtotal;
        $this->total_vat_0 = $vat0;
        $this->total_vat_9 = $vat9;
        $this->total_vat_21 = $vat21;
        $this->total = $total;
    }

    /**
     * Automatic dispatcher: calls the appropriate calculation method.
     */
    public function calculateTotals(): void
    {
        if ($this instanceof InvoiceItem) {
            $this->calculateItemTotals();
        }

        if ($this instanceof Invoice) {
            $this->calculateInvoiceTotals();
        }
    }
}
