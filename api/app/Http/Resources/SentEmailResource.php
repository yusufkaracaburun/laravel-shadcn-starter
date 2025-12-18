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
            'id'           => $this->id,
            'hash'         => $this->hash,
            'headers'      => $this->headers,
            'subject'      => $this->subject,
            'content'      => $this->content ? mb_substr($this->content, 0, 200) . (mb_strlen($this->content) > 200 ? '...' : '') : null,
            'opens'        => $this->opens,
            'clicks'       => $this->clicks,
            'message_id'   => $this->message_id,
            'sender_email' => $this->sender_email,
            'sender_name'  => $this->sender_name,
            'first_open'   => $this->formatDateTime($this->first_open),
            'first_click'  => $this->formatDateTime($this->first_click),

            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
        ];
    }
}
