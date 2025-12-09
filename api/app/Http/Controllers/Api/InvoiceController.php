<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Filters\SearchFilter;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\InvoiceResource;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Invoices\IndexInvoiceRequest;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
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

        $perPage = (int) ($request->input('per_page', 15));

        $invoices = $this->buildQuery(
            Invoice::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('invoice_number'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('date'),
                AllowedFilter::exact('date_due'),
                AllowedFilter::scope('between', 'date'),
                AllowedFilter::scope('date', 'date'),
                AllowedFilter::scope('dateDue', 'date_due'),
                AllowedFilter::custom('search', new SearchFilter(Invoice::$searchable)),
            ],
            allowedSorts: [
                'id',
                'invoice_number',
                'customer_id',
                'status',
                'date',
                'date_due',
                'subtotal',
                'total',
                'created_at',
                'updated_at',
            ],
            allowedIncludes: ['customer']
        )->paginate($perPage);

        return ApiResponse::success(InvoiceResource::collection($invoices));
    }

    /**
     * Store a newly created invoice.
     *
     * @authenticated
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = Invoice::query()->create($request->validated());

        return ApiResponse::created(new InvoiceResource($invoice));
    }

    /**
     * Display the specified invoice.
     *
     * @authenticated
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice->load('customer');

        return ApiResponse::success(new InvoiceResource($invoice));
    }

    /**
     * Update the specified invoice.
     *
     * @authenticated
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $invoice->update($request->validated());

        return ApiResponse::success(new InvoiceResource($invoice));
    }

    /**
     * Remove the specified invoice.
     *
     * @authenticated
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return ApiResponse::noContent('Invoice deleted successfully');
    }
}
