<?php

declare(strict_types=1);

namespace App\Http\Resources\Customers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Enums\CustomerStatus;
use App\Http\Resources\BaseResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\Contacts\ContactResource;

/**
 * @mixin Customer
 */
final class CustomerResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'     => $this->id,
            'type'   => $this->type,
            'name'   => $this->name,
            'number' => mb_str_pad((string) $this->id, 6, '0', STR_PAD_LEFT),

            // Address fields
            'address'           => $this->address,
            'formatted_address' => $this->when(
                $this->address && $this->zipcode && $this->city,
                fn () => $this->formatted_address,
            ),
            'zipcode' => $this->zipcode,
            'city'    => $this->city,
            'country' => $this->country,

            // Contact / business info
            'email'            => $this->email,
            'phone'            => $this->phone,
            'status'           => $this->status,
            'status_formatted' => CustomerStatus::toArrayItem($this->status),
            'kvk_number'       => $this->kvk_number,
            'vat_number'       => $this->vat_number,
            'iban_number'      => $this->iban_number,

            // Timestamps
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),

            'primary_contact' => ContactResource::make($this->primary_contact),

            // Collections
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),

            // Counts
            'contacts_count' => $this->whenCounted('contacts'),
            'invoices_count' => $this->whenCounted('invoices'),
        ];
    }
}
