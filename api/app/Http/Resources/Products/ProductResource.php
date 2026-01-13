<?php

declare(strict_types=1);

namespace App\Http\Resources\Products;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

/**
 * @mixin Product
 */
final class ProductResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'unit_price'  => $this->unit_price,
            'vat_rate'    => $this->vat_rate,
            'unit'        => $this->unit,

            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }
}
