<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Services\Concerns\TransformsResources;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

final class InvoiceService extends BaseService implements InvoiceServiceInterface
{
    use TransformsResources;

    public function __construct(
        InvoiceRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): InvoiceCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): InvoiceCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): InvoiceResource
    {
        $invoice = $this->repository->find($id);

        return $this->toResource($invoice->load(['customer', 'items', 'payments', 'emails', 'activities.causer']));
    }

    public function create(array $data): InvoiceResource
    {
        // Extract items from data
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Create invoice
        $invoice = $this->repository->create($data);

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

        return $this->toResource($invoice->load('items'));
    }

    /**
     * @param Invoice $model
     */
    public function update(Model $model, array $data): InvoiceResource
    {
        // Extract items from data
        $items = $data['items'] ?? null;
        unset($data['items']);

        // Update invoice via repository
        $updated = $this->repository->update($model->id, $data);

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

        return $this->toResource($updated->load('items'));
    }

    /**
     * @param Invoice $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
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

    protected function getResourceClass(): string
    {
        return InvoiceResource::class;
    }

    protected function getCollectionClass(): string
    {
        return InvoiceCollection::class;
    }
}
