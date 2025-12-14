<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Status update event for broadcasting entity status changes via Reverb.
 *
 * This event can be used to broadcast status updates for any entity type
 * (invoices, payments, orders, etc.) to private or public channels.
 *
 * @example
 * ```php
 * // Broadcast invoice status update to private channel
 * event(new StatusUpdated(
 *     entityType: 'invoice',
 *     entityId: $invoice->id,
 *     status: 'paid',
 *     userId: $invoice->customer->user_id
 * ));
 *
 * // Broadcast payment status update to public channel
 * event(new StatusUpdated(
 *     entityType: 'payment',
 *     entityId: $payment->id,
 *     status: 'completed',
 *     isPublic: true
 * ));
 * ```
 */
final readonly class StatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $entityType  The type of entity (e.g., 'invoice', 'payment', 'order')
     * @param  int  $entityId  The ID of the entity
     * @param  string  $status  The new status value
     * @param  int|null  $userId  Optional user ID for private channel broadcasting
     * @param  bool  $isPublic  Whether to broadcast to a public channel
     */
    public function __construct(
        public string $entityType,
        public int $entityId,
        public string $status,
        public ?int $userId = null,
        public bool $isPublic = false,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel|PrivateChannel>
     */
    public function broadcastOn(): array
    {
        if ($this->isPublic) {
            return [
                new Channel("{$this->entityType}s"),
            ];
        }

        if ($this->userId !== null) {
            return [
                new PrivateChannel("{$this->entityType}.{$this->entityId}"),
            ];
        }

        return [
            new Channel("{$this->entityType}s"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return "{$this->entityType}.status.updated";
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'status' => $this->status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
