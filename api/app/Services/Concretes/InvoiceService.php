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
        InvoiceRepositoryInterface $repo
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
        $invoice = $this->invoiceRepository->create($data);

        return new InvoiceResource($invoice);
    }

    public function updateInvoice(Invoice $invoice, array $data, ?int $teamId = null): InvoiceResource
    {
        try {
            $updated = $this->invoiceRepository->update($invoice->id, $data);

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
}
