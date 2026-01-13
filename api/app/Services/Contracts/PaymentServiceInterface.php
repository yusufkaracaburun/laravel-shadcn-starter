<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentCollection;

interface PaymentServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PaymentCollection;

    public function getAll(array $columns = ['*']): PaymentCollection;

    public function findById(int $id): PaymentResource;

    public function createPayment(array $data): PaymentResource;

    public function updatePayment(Payment $payment, array $data): PaymentResource;

    public function deletePayment(Payment $payment): bool;
}
