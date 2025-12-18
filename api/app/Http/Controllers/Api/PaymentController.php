<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Requests\Payments\IndexPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Services\Contracts\PaymentServiceInterface;
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

    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
    ) {}

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

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $payments = $this->paymentService->getPaginated($perPage);

        return ApiResponse::success($payments);
    }

    /**
     * Store a newly created payment.
     *
     * @authenticated
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $payment = $this->paymentService->createPayment($request->validated());

        return ApiResponse::created($payment);
    }

    /**
     * Display the specified payment.
     *
     * @authenticated
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        $paymentResource = $this->paymentService->findById($payment->id);

        return ApiResponse::success($paymentResource);
    }

    /**
     * Update the specified payment.
     *
     * @authenticated
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('update', $payment);

        $paymentResource = $this->paymentService->updatePayment($payment, $request->validated());

        return ApiResponse::success($paymentResource);
    }

    /**
     * Remove the specified payment.
     *
     * @authenticated
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->deletePayment($payment);

        return ApiResponse::noContent('Payment deleted successfully');
    }
}
