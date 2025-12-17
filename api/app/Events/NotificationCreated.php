<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

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
final readonly class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ?int $userId = null,
        public string $message = '',
        public string $type = 'info',
        public ?string $title = null,
        public ?string $description = null,
        public array $data = [],
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
                new Channel('notifications'),
            ];
        }

        if ($this->userId) {
            return [
                new PrivateChannel('user.' . $this->userId),
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
            'message'     => $this->message,
            'type'        => $this->type,
            'title'       => $this->title,
            'description' => $this->description,
            'data'        => $this->data,
        ];
    }
}
