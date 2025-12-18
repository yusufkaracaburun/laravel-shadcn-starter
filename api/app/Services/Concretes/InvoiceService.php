<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Invoice;
use App\Services\BaseService;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

final class InvoiceService extends BaseService implements InvoiceServiceInterface
{
    private readonly InvoiceRepositoryInterface $invoiceRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(
        InvoiceRepositoryInterface $repo,
    ) {
        $this->setRepository($repo);
        $this->invoiceRepository = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): InvoiceCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->invoiceRepository->withRequest($request);

        $paginated = $this->invoiceRepository->query()->paginate($perPage);

        return new InvoiceCollection($paginated);
    }

    public function findById(int $invoiceId, ?int $teamId = null): InvoiceResource
    {
        try {
            $invoice = $this->invoiceRepository->findOrFail($invoiceId);

            return new InvoiceResource($invoice);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
    }

    public function createInvoice(array $data, ?int $teamId = null): InvoiceResource
    {
        // Extract items from data
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Create invoice
        $invoice = $this->invoiceRepository->create($data);

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

        return new InvoiceResource($invoice);
    }

    public function updateInvoice(Invoice $invoice, array $data, ?int $teamId = null): InvoiceResource
    {
        try {
            // Extract items from data
            $items = $data['items'] ?? null;
            unset($data['items']);

            // Update invoice
            $updated = $this->invoiceRepository->update($invoice->id, $data);

            // Update items if provided
            if ($items !== null) {
                // Delete existing items
                $updated->items()->delete();

                // Create new items
                if (!empty($items)) {
                    foreach ($items as $index => $itemData) {
                        // Set default sort_order if not provided
                        if (!isset($itemData['sort_order'])) {
                            $itemData['sort_order'] = $index;
                        }

                        $updated->items()->create($itemData);
                    }
                }

                // Recalculate invoice totals
                $updated->refresh();
                $updated->calculateInvoiceTotals();
                $updated->save();
            }

            return new InvoiceResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
    }

    public function deleteInvoice(Invoice $invoice): bool
    {
        try {
            $this->invoiceRepository->delete($invoice->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Invoice not found');
        }
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
