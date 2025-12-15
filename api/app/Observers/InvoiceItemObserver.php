<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\InvoiceItem;

/**
 * Observer for InvoiceItem model events.
 *
 * Handles recalculation of invoice totals when items are created, updated, or deleted.
 */
final class InvoiceItemObserver extends BaseObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     * Executed after creating (INSERT).
     */
    public function created(InvoiceItem $item): void
    {
        $this->log('created', $item);
        $this->recalculateInvoiceTotals($item);
    }

    /**
     * Handle the InvoiceItem "updated" event.
     * Executed after updating (UPDATE).
     */
    public function updated(InvoiceItem $item): void
    {
        $this->log('updated', $item);
        $this->recalculateInvoiceTotals($item);
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     * Executed after deleting.
     */
    public function deleted(InvoiceItem $item): void
    {
        $this->log('deleted', $item);
        $this->recalculateInvoiceTotals($item);
    }

    /**
     * Recalculate invoice totals after item changes.
     */
    private function recalculateInvoiceTotals(InvoiceItem $item): void
    {
        $invoice = $item->invoice;

        if ($invoice) {
            $invoice->refresh();
            $invoice->calculateInvoiceTotals();
            $invoice->saveQuietly(); // Use saveQuietly to avoid triggering observers recursively
        }
    }
}
