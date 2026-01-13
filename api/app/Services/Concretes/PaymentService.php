<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentCollection;
use App\Services\Contracts\PaymentServiceInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentService extends BaseService implements PaymentServiceInterface
{
    private readonly PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $repo)
    {
        $this->setRepository($repo);
        $this->paymentRepository = $repo;
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PaymentCollection
    {
        return new PaymentCollection(
            $this->paymentRepository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): PaymentCollection
    {
        return new PaymentCollection(
            $this->paymentRepository->all($columns),
        );
    }

    public function findById(int $id): PaymentResource
    {
        $payment = $this->paymentRepository->find($id);

        return new PaymentResource($payment->load(['invoice', 'customer']));
    }

    public function createPayment(array $data): PaymentResource
    {
        return new PaymentResource(
            parent::create($data),
        );
    }

    public function updatePayment(Payment $payment, array $data): PaymentResource
    {
        return new PaymentResource(
            parent::update($payment, $data),
        );
    }

    public function deletePayment(Payment $payment): bool
    {
        return parent::delete($payment);
    }
}
