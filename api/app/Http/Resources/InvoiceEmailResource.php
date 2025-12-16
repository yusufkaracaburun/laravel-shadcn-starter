<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\EmailStatus;
use App\Models\InvoiceEmail;
use Illuminate\Http\Request;

/**
 * @mixin InvoiceEmail
 */
final class InvoiceEmailResource extends BaseResource
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
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->body ? mb_substr($this->body, 0, 200).(mb_strlen($this->body) > 200 ? '...' : '') : null,
            'status_formatted' => EmailStatus::toArrayItem($this->status),
            'status' => $this->status,
            'sent_at' => $this->formatDateTime($this->sent_at),
            'opened_at' => $this->formatDateTime($this->opened_at),
            'clicked_at' => $this->formatDateTime($this->clicked_at),
            'error_message' => $this->error_message,

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
