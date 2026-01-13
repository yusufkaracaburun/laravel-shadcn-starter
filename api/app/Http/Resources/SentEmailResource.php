<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SentEmail;
use Illuminate\Http\Request;

/**
 * @mixin SentEmail
 */
final class SentEmailResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload(Request $request): array
    {
        return [
            'id'              => $this->id,
            'hash'            => $this->hash,
            'headers'         => $this->headers,
            'subject'         => $this->subject,
            'content'         => $this->content ?? 'No content',
            'opens'           => $this->opens,
            'clicks'          => $this->clicks,
            'message_id'      => $this->message_id,
            'sender_email'    => $this->sender_email,
            'sender_name'     => $this->sender_name,
            'recipient_email' => $this->recipient_email,
            'recipient_name'  => $this->recipient_name,
            'clicked_at'      => $this->formatDateTime($this->clicked_at),
            'opened_at'       => $this->formatDateTime($this->opened_at),

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
