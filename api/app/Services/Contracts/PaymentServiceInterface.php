<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentCollection;

interface PaymentServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PaymentCollection;

    public function getAll(): PaymentCollection;

    public function findById(int $paymentId, ?int $teamId = null): PaymentResource;

    public function createPayment(array $data, ?int $teamId = null): PaymentResource;

    public function updatePayment(Payment $payment, array $data, ?int $teamId = null): PaymentResource;

    public function deletePayment(Payment $payment): bool;
}
