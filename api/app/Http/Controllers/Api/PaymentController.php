<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Filters\SearchFilter;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Payments\IndexPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Requests\Payments\UpdatePaymentRequest;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class PaymentController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    /**
     * Display a listing of payments with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/payments?filter[status]=paid&sort=-date&include=customer,invoice
     *
     * @authenticated
     */
    public function index(IndexPaymentRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $perPage = (int) ($request->input('per_page', 15));

        $payments = $this->buildQuery(
            Payment::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('payment_number'),
                AllowedFilter::exact('invoice_id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('method'),
                AllowedFilter::exact('provider'),
                AllowedFilter::scope('date', 'date'),
                AllowedFilter::scope('paid_at', 'paid_at'),
                AllowedFilter::scope('between', 'date'),
                AllowedFilter::custom('search', new SearchFilter(Payment::$searchable)),
            ],
            allowedSorts: [
                'id',
                'payment_number',
                'invoice_id',
                'customer_id',
                'status',
                'date',
                'paid_at',
                'amount',
                'method',
                'provider',
                'created_at',
                'updated_at',
            ],
            allowedIncludes: ['customer', 'invoice']
        )->paginate($perPage);

        return ApiResponse::success(new PaymentCollection($payments));
    }

    /**
     * Store a newly created payment.
     *
     * @authenticated
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $payment = Payment::query()->create($request->validated());

        return ApiResponse::created(new PaymentResource($payment));
    }

    /**
     * Display the specified payment.
     *
     * @authenticated
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        $payment->load(['customer', 'invoice']);

        return ApiResponse::success(new PaymentResource($payment));
    }

    /**
     * Update the specified payment.
     *
     * @authenticated
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('update', $payment);

        $payment->update($request->validated());

        return ApiResponse::success(new PaymentResource($payment));
    }

    /**
     * Remove the specified payment.
     *
     * @authenticated
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return ApiResponse::noContent('Payment deleted successfully');
    }
}
