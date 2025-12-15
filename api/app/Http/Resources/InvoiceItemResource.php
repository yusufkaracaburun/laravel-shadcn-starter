<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;

/**
 * @mixin InvoiceItem
 */
final class InvoiceItemResource extends BaseResource
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
            'invoice_id' => $this->invoice_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'vat_rate' => $this->vat_rate,
            'total_excl_vat' => $this->total_excl_vat,
            'total_vat' => $this->total_vat,
            'total_incl_vat' => $this->total_incl_vat,
            'sort_order' => $this->sort_order,

            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }
}
