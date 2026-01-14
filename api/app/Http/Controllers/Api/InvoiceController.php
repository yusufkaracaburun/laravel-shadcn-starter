<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\Invoices\IndexInvoiceRequest;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Services\Contracts\CustomerServiceInterface;

final class InvoiceController extends BaseApiController
{
    public function __construct(
        private readonly InvoiceServiceInterface $invoiceService,
        private readonly ProductServiceInterface $itemService,
        private readonly CustomerServiceInterface $customerService,
    ) {}

    /**
     * Display a listing of invoices with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/invoices?filter[status]=draft&sort=-date&include=customer
     *
     * @authenticated
     */
    public function index(IndexInvoiceRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        return $this->respondWithCollection(
            $this->invoiceService->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created invoice.
     *
     * @authenticated
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        return $this->respondCreated(
            $this->invoiceService->create($request->validated()),
        );
    }

    /**
     * Display the specified invoice.
     *
     * @authenticated
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return $this->respondWithResource(
            $this->invoiceService->findById($invoice->id),
        );
    }

    /**
     * Update the specified invoice.
     *
     * @authenticated
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        return $this->respondWithResource(
            $this->invoiceService->update($invoice, $request->validated()),
        );
    }

    /**
     * Remove the specified invoice.
     *
     * @authenticated
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $this->invoiceService->delete($invoice);

        return $this->respondNoContent('Invoice deleted successfully');
    }

    /**
     * Get prerequisites for creating a new invoice.
     * Returns all items, all customers, and the next invoice number.
     *
     * @authenticated
     */
    public function prerequisites(): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        // Get all items from ItemService
        $items = $this->itemService->getAll();

        // Get all customers from CustomerService
        $customers = $this->customerService->getAll();

        // Get next invoice number from InvoiceService
        $nextInvoiceNumber = $this->invoiceService->getNextInvoiceNumber();

        return $this->respondWithPrerequisites([
            'items'               => $items,
            'customers'           => $customers,
            'next_invoice_number' => $nextInvoiceNumber,
        ]);
    }

    public function asHtml(Invoice $invoice): Factory|View
    {
        $invoice = $this->invoiceService->findById($invoice->id);

        return view('pdf.invoice', [
            'invoice' => $invoice,
        ]);
    }

    public function previewPdf(Invoice $invoice)
    {
        $invoice = $this->invoiceService->findById($invoice->id);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->setPaper('a4');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="factuur_' . $invoice->invoice_number . '.pdf"');
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice = $this->invoiceService->findById($invoice->id);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])
            ->setPaper('a4')
            ->download("factuur_{$invoice->invoice_number}.pdf");
    }
}
