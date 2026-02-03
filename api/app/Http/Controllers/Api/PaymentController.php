<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Payments\IndexPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Services\Contracts\PaymentServiceInterface;
use App\Http\Requests\Payments\UpdatePaymentRequest;

final class PaymentController extends BaseApiController
{
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

        return $this->respondWithCollection(
            $this->paymentService->getPaginatedByRequest($request),
        );
    }

    /**
     * Store a newly created payment.
     *
     * @authenticated
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        return $this->respondCreated(
            $this->paymentService->create($request->validated()),
        );
    }

    /**
     * Display the specified payment.
     *
     * @authenticated
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return $this->respondWithResource(
            $this->paymentService->findById($payment->id),
        );
    }

    /**
     * Update the specified payment.
     *
     * @authenticated
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('update', $payment);

        return $this->respondWithResource(
            $this->paymentService->update($payment, $request->validated()),
        );
    }

    /**
     * Remove the specified payment.
     *
     * @authenticated
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->delete($payment);

        return $this->respondNoContent('Payment deleted successfully');
    }
}
