<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Payment;
use App\Services\BaseService;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentService extends BaseService implements PaymentServiceInterface
{
    private readonly PaymentRepositoryInterface $paymentRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(
        PaymentRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
        $this->paymentRepository = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): PaymentCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->paymentRepository->withRequest($request);

        $paginated = $this->paymentRepository->query()->paginate($perPage);

        return new PaymentCollection($paginated);
    }

    public function findById(int $paymentId, ?int $teamId = null): PaymentResource
    {
        try {
            $payment = $this->paymentRepository->findOrFail($paymentId);

            return new PaymentResource($payment);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }

    public function createPayment(array $data, ?int $teamId = null): PaymentResource
    {
        $payment = $this->paymentRepository->create($data);

        return new PaymentResource($payment);
    }

    public function updatePayment(Payment $payment, array $data, ?int $teamId = null): PaymentResource
    {
        try {
            $updated = $this->paymentRepository->update($payment->id, $data);

            return new PaymentResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }

    public function deletePayment(Payment $payment): bool
    {
        try {
            $this->paymentRepository->delete($payment->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Payment not found');
        }
    }
}
