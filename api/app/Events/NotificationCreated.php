<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notification event for user notifications via Reverb.
 * 
 * This event will automatically show toast notifications in the frontend.
 * 
 * @example
 * ```php
 * // Send notification to a specific user
 * event(new NotificationCreated(
 *     userId: $user->id,
 *     message: 'Your order has been shipped!',
 *     type: 'success'
 * ));
 * 
 * // Send public notification
 * event(new NotificationCreated(
 *     message: 'System maintenance in 5 minutes',
 *     type: 'warning',
 *     isPublic: true
 * ));
 * ```
 */
final class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly ?int $userId = null,
        public readonly string $message = '',
        public readonly string $type = 'info',
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly array $data = [],
        public readonly bool $isPublic = false,
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
                new Channel('notifications'),
            ];
        }

        if ($this->userId) {
            return [
                new PrivateChannel('user.'.$this->userId),
            ];
        }

        return [
            new Channel('notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'data' => $this->data,
        ];
    }
}

