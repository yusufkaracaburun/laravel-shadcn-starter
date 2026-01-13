<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

 // Added this import

final class InvoiceService extends BaseService implements InvoiceServiceInterface
{
    private readonly InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->invoiceRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): InvoiceCollection
    {
        return new InvoiceCollection(
            $this->invoiceRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): InvoiceCollection
    {
        return new InvoiceCollection(
            $this->invoiceRepository->all($columns),
        );
    }

    public function findById(int $id): InvoiceResource
    {
        $invoice = $this->invoiceRepository->find($id);

        return new InvoiceResource($invoice->load(['customer', 'items', 'payments', 'emails', 'activities.causer']));
    }

    public function createInvoice(array $data): InvoiceResource
    {
        // Extract items from data
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Create invoice
        $invoice = parent::create($data);

        // Create invoice items if provided
        foreach ($items as $index => $itemData) {
            // Set default sort_order if not provided
            if (!isset($itemData['sort_order'])) {
                $itemData['sort_order'] = $index;
            }

            $invoice->items()->create($itemData);
        }

        // Recalculate invoice totals
        $invoice->refresh();
        $invoice->calculateInvoiceTotals();
        $invoice->save();

        return new InvoiceResource($invoice->load('items'));
    }

    public function updateInvoice(Invoice $invoice, array $data): InvoiceResource
    {
        // Extract items from data
        $items = $data['items'] ?? null;
        unset($data['items']);

        // Update invoice
        $updated = parent::update($invoice, $data);

        // Update items if provided
        if ($items !== null) {
            // Delete existing items
            $updated->items()->delete();

            // Create new items
            foreach ($items as $index => $itemData) {
                // Set default sort_order if not provided
                if (!isset($itemData['sort_order'])) {
                    $itemData['sort_order'] = $index;
                }

                $updated->items()->create($itemData);
            }

            // Recalculate invoice totals
            $updated->refresh();
            $updated->calculateInvoiceTotals();
            $updated->save();
        }

        return new InvoiceResource($updated->load('items'));
    }

    public function deleteInvoice(Invoice $invoice): bool
    {
        return parent::delete($invoice);
    }

    public function getNextInvoiceNumber(string $prefix = 'INV', ?int $year = null): string
    {
        $year ??= now()->year;

        $lastNumber = Invoice::query()->whereYear('date', $year)
            ->latest('date')
            ->value('invoice_number');

        $next = 1;

        if ($lastNumber && preg_match("/{$prefix}-{$year}-(\d+)/", (string) $lastNumber, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $next);
    }
}
