<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Invoices\IndexInvoiceRequest;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class InvoiceController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly InvoiceServiceInterface $invoiceService
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

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $invoices = $this->invoiceService->getPaginated($perPage);

        return ApiResponse::success($invoices);
    }

    /**
     * Store a newly created invoice.
     *
     * @authenticated
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->createInvoice($request->validated());

        return ApiResponse::created($invoice);
    }

    /**
     * Display the specified invoice.
     *
     * @authenticated
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoiceResource = $this->invoiceService->findById($invoice->id);

        return ApiResponse::success($invoiceResource);
    }

    /**
     * Update the specified invoice.
     *
     * @authenticated
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $invoiceResource = $this->invoiceService->updateInvoice($invoice, $request->validated());

        return ApiResponse::success($invoiceResource);
    }

    /**
     * Remove the specified invoice.
     *
     * @authenticated
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $this->invoiceService->deleteInvoice($invoice);

        return ApiResponse::noContent('Invoice deleted successfully');
    }
}
