<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;

/**
 * @mixin Invoice
 */
final class InvoiceResource extends BaseResource
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
            'invoice_number' => $this->invoice_number,
            'date' => $this->formatDate($this->date),
            'due_days' => $this->due_days,
            'date_due' => $this->formatDate($this->date_due),
            'status_formatted' => InvoiceStatus::toArrayItem($this->status),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'total_vat_0' => $this->total_vat_0,
            'total_vat_9' => $this->total_vat_9,
            'total_vat_21' => $this->total_vat_21,
            'total' => $this->total,
            'notes' => $this->notes,

            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => InvoiceItemCollection::make($this->whenLoaded('items')),

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
