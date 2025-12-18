<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Entity status update event for broadcasting entity status changes via Reverb.
 *
 * This event provides a flexible way to broadcast status updates for any entity
 * with additional metadata. Useful for server status, system health, or any
 * entity that needs status tracking with extra context.
 *
 * @example
 * ```php
 * // Broadcast server status update
 * event(new EntityStatusUpdated(
 *     entityType: 'server',
 *     entityId: $server->id,
 *     status: 'online',
 *     metadata: ['cpu' => 45, 'memory' => 60]
 * ));
 *
 * // Broadcast system health status
 * event(new EntityStatusUpdated(
 *     entityType: 'system',
 *     entityId: 1,
 *     status: 'healthy',
 *     metadata: ['uptime' => '99.9%'],
 *     isPublic: true
 * ));
 * ```
 */
final readonly class EntityStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $entityType  The type of entity (e.g., 'server', 'system', 'service')
     * @param  int  $entityId  The ID of the entity
     * @param  string  $status  The new status value
     * @param  array<string, mixed>  $metadata  Additional metadata to include in the broadcast
     * @param  int|null  $userId  Optional user ID for private channel broadcasting
     * @param  bool  $isPublic  Whether to broadcast to a public channel
     */
    public function __construct(
        public string $entityType,
        public int $entityId,
        public string $status,
        public array $metadata = [],
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
            'entity_id'   => $this->entityId,
            'status'      => $this->status,
            'metadata'    => $this->metadata,
            'updated_at'  => now()->toIso8601String(),
        ];
    }
}
