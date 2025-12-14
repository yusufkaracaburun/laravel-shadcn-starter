<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;

/**
 * @mixin Payment
 */
final class PaymentResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'date' => $this->formatDateTime($this->date),
            'amount' => $this->amount,
            'method' => $this->method,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'status_formatted' => PaymentStatus::toArrayItem($this->status),
            'status' => $this->status,
            'paid_at' => $this->formatDateTime($this->paid_at),
            'refunded_at' => $this->formatDateTime($this->refunded_at),

            // Relations
            'invoice_id' => $this->invoice_id,
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
