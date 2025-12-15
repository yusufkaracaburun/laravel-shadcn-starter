<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Payment;
use App\Services\BaseServiceInterface;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;

interface PaymentServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated payments with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): PaymentCollection;

    /**
     * Find a payment by ID with relationships loaded.
     */
    public function findById(int $paymentId, ?int $teamId = null): PaymentResource;

    /**
     * Create a new payment with optional team context.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPayment(array $data, ?int $teamId = null): PaymentResource;

    /**
     * Update a payment by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePayment(Payment $payment, array $data, ?int $teamId = null): PaymentResource;

    /**
     * Delete a payment by model instance.
     */
    public function deletePayment(Payment $payment): bool;
}
