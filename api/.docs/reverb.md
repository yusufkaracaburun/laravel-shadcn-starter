# Laravel Reverb Usage Guide

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Server-Side Usage](#server-side-usage)
- [Client-Side Usage](#client-side-usage)
- [Channel Types](#channel-types)
- [Examples](#examples)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Introduction

Laravel Reverb provides real-time WebSocket communication for your Laravel application. It allows you to broadcast events to connected clients instantly, enabling features like live notifications, real-time updates, chat functionality, and more.

### How It Works

1. **Server**: Laravel broadcasts events through Reverb
2. **Reverb Server**: WebSocket server that manages connections and delivers messages
3. **Client**: Vue.js application connects via Laravel Echo and receives events in real-time

## Configuration

### Environment Variables

**API `.env`** (`api/.env`):

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=9999
REVERB_SCHEME=http
REVERB_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
# Or use '*' to allow all origins (development only - configured in config/reverb.php)
# For production, always specify exact origins for security
```

**Client `.env`** (`client/.env`):

```env
VITE_REVERB_ENABLED=true
VITE_REVERB_APP_KEY=your-app-key          # Same as REVERB_APP_KEY
VITE_REVERB_HOST=localhost                # Same as REVERB_HOST
VITE_REVERB_PORT=9999                      # Same as REVERB_PORT
VITE_REVERB_SCHEME=http                    # Same as REVERB_SCHEME
```

> **Important**: The client only needs the **public** credentials. Never put `REVERB_APP_SECRET` or `REVERB_APP_ID` in the client `.env`.

### Starting the Reverb Server

The Reverb server is automatically started when you run:

```bash
composer run dev
```

Or start it manually:

```bash
php artisan reverb:start
```

For production, use a process manager like Supervisor to keep Reverb running.

## Server-Side Usage

### Creating Broadcast Events

Create events that implement `ShouldBroadcast`:

```bash
php artisan make:event OrderShipped
```

Example event:

```php
<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $trackingNumber,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    /**
     * The event's broadcast name.
     * Defaults to the class name if not specified.
     */
    public function broadcastAs(): string
    {
        return 'order.shipped';
    }

    /**
     * Get the data to broadcast.
     * By default, all public properties are broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'tracking_number' => $this->trackingNumber,
            'shipped_at' => now()->toIso8601String(),
        ];
    }
}
```

### Broadcasting Events

**For immediate broadcasting** (recommended for real-time updates), use `ShouldBroadcastNow`:

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderShipped implements ShouldBroadcastNow
{
    // Event will broadcast immediately
}
```

Broadcast events using the `event()` helper:

```php
use App\Events\OrderShipped;

// Broadcast immediately (if using ShouldBroadcastNow)
event(new OrderShipped($orderId, $trackingNumber));
```

**For queued broadcasting** (recommended for heavy operations), use `ShouldBroadcast` with `ShouldQueue`:

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderShipped implements ShouldBroadcast, ShouldQueue
{
    // Event will be queued and broadcast asynchronously
}
```

### Testing Broadcasts

A test command is available to quickly test your Reverb setup:

```bash
# Broadcast with default message
php artisan reverb:broadcast

# Broadcast with custom message
php artisan reverb:broadcast "Your custom message here"
```

This command broadcasts to the `example-channel` using the `ExampleBroadcastEvent` class. You can use the included example component (`client/src/components/event-listeners/example-reverb-listener.vue`) to see the messages in real-time.

### Broadcasting from Models

You can broadcast events automatically when models are created, updated, or deleted:

```php
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Order extends Model
{
    protected $dispatchesEvents = [
        'created' => OrderCreated::class,
        'updated' => OrderUpdated::class,
    ];
}
```

## Client-Side Usage

### Using the Echo Composable

Import and use the `useEcho()` composable in your Vue components:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useEcho } from '@/composables/use-echo'

const echo = useEcho()

onMounted(() => {
  if (!echo) {
    console.warn('Echo is not available')
    return
  }

  // Listen to events
  echo.channel('orders')
    .listen('.order.shipped', (data) => {
      console.log('Order shipped:', data)
    })
})

onUnmounted(() => {
  if (echo) {
    echo.leave('orders')
  }
})
</script>
```

### Listening to Events

**Public Channels**:

```typescript
echo.channel('notifications')
  .listen('.notification.created', (data) => {
    console.log('New notification:', data)
  })
```

**Private Channels** (requires authentication):

```typescript
echo.private('user.123')
  .listen('.user.updated', (data) => {
    console.log('User updated:', data)
  })
```

**Presence Channels** (shows who's present):

```typescript
echo.join('chat-room')
  .here((users) => {
    console.log('Users here:', users)
  })
  .joining((user) => {
    console.log('User joined:', user)
  })
  .leaving((user) => {
    console.log('User left:', user)
  })
  .listen('.message.sent', (data) => {
    console.log('New message:', data)
  })
```

### Event Naming Convention

When using `broadcastAs()`, prefix the event name with a dot (`.`) on the client:

```php
// Server: broadcastAs() returns 'order.shipped'
public function broadcastAs(): string
{
    return 'order.shipped';
}
```

```typescript
// Client: Use '.order.shipped'
echo.channel('orders')
  .listen('.order.shipped', (data) => { ... })
```

If you don't use `broadcastAs()`, the event name is the class name:

```typescript
// Server: No broadcastAs(), class is OrderShipped
// Client: Use 'OrderShipped'
echo.channel('orders')
  .listen('OrderShipped', (data) => { ... })
```

## Channel Types

### Public Channels

Public channels are accessible to anyone. No authentication required.

**Server**:

```php
public function broadcastOn(): array
{
    return [
        new Channel('public-orders'),
    ];
}
```

**Client**:

```typescript
echo.channel('public-orders')
  .listen('.order.created', (data) => { ... })
```

### Private Channels

Private channels require authentication. Users must be authenticated to subscribe.

**Server** - Define authorization in `routes/channels.php`:

```php
use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function (User $user, string $orderId): bool {
    return $user->orders()->where('id', $orderId)->exists();
});
```

**Server** - Use in event:

```php
public function broadcastOn(): array
{
    return [
        new PrivateChannel('order.'.$this->orderId),
    ];
}
```

**Client**:

```typescript
echo.private(`order.${orderId}`)
  .listen('.order.updated', (data) => { ... })
```

### Presence Channels

Presence channels are private channels that also broadcast who is present.

**Server** - Define in `routes/channels.php`:

```php
Broadcast::channel('chat.{roomId}', function (User $user, string $roomId): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ];
});
```

**Server** - Use in event:

```php
public function broadcastOn(): array
{
    return [
        new PresenceChannel('chat.'.$this->roomId),
    ];
}
```

**Client**:

```typescript
echo.join(`chat.${roomId}`)
  .here((users) => {
    // Users currently in the channel
    console.log('Users here:', users)
  })
  .joining((user) => {
    // User just joined
    console.log('User joined:', user)
  })
  .leaving((user) => {
    // User just left
    console.log('User left:', user)
  })
  .listen('.message.sent', (data) => {
    console.log('New message:', data)
  })
```

## Examples

### Example 1: Real-time Notifications

**Server** - Create event:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $message,
        public readonly string $type = 'info',
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
```

**Server** - Broadcast:

```php
use App\Events\NotificationCreated;

event(new NotificationCreated(
    userId: $user->id,
    message: 'Your order has been shipped!',
    type: 'success'
));
```

**Client** - Listen:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useEcho } from '@/composables/use-echo'
import { useAuthStore } from '@/stores/auth'

const echo = useEcho()
const authStore = useAuthStore()

onMounted(() => {
  if (!echo || !authStore.user) return

  echo.private(`user.${authStore.user.id}`)
    .listen('.notification.created', (data) => {
      // Show notification to user
      console.log('New notification:', data)
    })
})

onUnmounted(() => {
  if (echo && authStore.user) {
    echo.leave(`user.${authStore.user.id}`)
  }
})
</script>
```

### Example 2: Live Order Updates

**Server** - Create event:

```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->order->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'updated_at' => $this->order->updated_at->toIso8601String(),
        ];
    }
}
```

**Client** - Listen:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useEcho } from '@/composables/use-echo'

const props = defineProps<{
  orderId: number
}>()

const echo = useEcho()
const orderStatus = ref<string>('pending')

onMounted(() => {
  if (!echo) return

  echo.private(`order.${props.orderId}`)
    .listen('.order.status.updated', (data) => {
      orderStatus.value = data.status
    })
})

onUnmounted(() => {
  if (echo) {
    echo.leave(`order.${props.orderId}`)
  }
})
</script>

<template>
  <div>
    <p>Order Status: {{ orderStatus }}</p>
  </div>
</template>
```

### Example 3: Chat Room

**Server** - Define presence channel:

```php
// routes/channels.php
Broadcast::channel('chat.{roomId}', function (User $user, string $roomId): array {
    // Verify user can access this room
    if (!$user->canAccessRoom($roomId)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url,
    ];
});
```

**Server** - Create message event:

```php
<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Message $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.'.$this->message->room_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
```

**Client** - Chat component:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useEcho } from '@/composables/use-echo'

const props = defineProps<{
  roomId: number
}>()

const echo = useEcho()
const messages = ref<Array<any>>([])
const users = ref<Array<any>>([])

onMounted(() => {
  if (!echo) return

  echo.join(`chat.${props.roomId}`)
    .here((usersList) => {
      users.value = usersList
    })
    .joining((user) => {
      users.value.push(user)
    })
    .leaving((user) => {
      users.value = users.value.filter(u => u.id !== user.id)
    })
    .listen('.message.sent', (data) => {
      messages.value.push(data)
    })
})

onUnmounted(() => {
  if (echo) {
    echo.leave(`chat.${props.roomId}`)
  }
})
</script>

<template>
  <div>
    <div>Users in room: {{ users.length }}</div>
    <div v-for="message in messages" :key="message.id">
      {{ message.content }}
    </div>
  </div>
</template>
```

## Best Practices

### 1. Choose the Right Broadcasting Interface

**For immediate real-time updates** (most common):
```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderShipped implements ShouldBroadcastNow
{
    // Event broadcasts immediately
}
```

**For heavy operations** (queue the broadcast):
```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderShipped implements ShouldBroadcast, ShouldQueue
{
    // Event will be queued and broadcast asynchronously
}
```

### 2. Use Specific Channel Names

Use descriptive, specific channel names:

```php
// Good
new PrivateChannel('order.'.$orderId)
new PresenceChannel('chat.'.$roomId)

// Bad
new Channel('updates')
new Channel('data')
```

### 3. Clean Up Listeners

Always clean up listeners when components unmount:

```typescript
onUnmounted(() => {
  if (echo) {
    echo.leave('channel-name')
  }
})
```

### 4. Handle Echo Availability

Always check if Echo is available before using it:

```typescript
const echo = useEcho()

if (!echo) {
  console.warn('Echo is not available')
  return
}
```

### 5. Use TypeScript Types

Define types for your event data:

```typescript
interface OrderShippedData {
  order_id: number
  tracking_number: string
  shipped_at: string
}

echo.channel('orders')
  .listen('.order.shipped', (data: OrderShippedData) => {
    // TypeScript will provide autocomplete and type checking
    console.log(data.order_id)
  })
```

### 6. Error Handling

Handle connection errors:

```typescript
echo.connector.pusher.connection.bind('error', (err: any) => {
  console.error('Connection error:', err)
})

echo.connector.pusher.connection.bind('connected', () => {
  console.log('Connected to Reverb')
})
```

## Troubleshooting

### Reverb Server Won't Start

**Port already in use**:

```bash
# Check what's using the port
lsof -i :9999

# Kill the process or change REVERB_PORT in .env
```

**Solution**: Change `REVERB_PORT` in your `.env` to an available port.

### Client Can't Connect

1. **Check Reverb is running**:
   ```bash
   php artisan reverb:start
   ```

2. **Verify environment variables match**:
   - `VITE_REVERB_APP_KEY` must match `REVERB_APP_KEY`
   - `VITE_REVERB_HOST` must match `REVERB_HOST`
   - `VITE_REVERB_PORT` must match `REVERB_PORT`

3. **Check CORS configuration**:
   - Ensure your client origin is in `REVERB_ALLOWED_ORIGINS`

4. **Check browser console**:
   - Look for WebSocket connection errors
   - Verify authentication endpoint is accessible

### Events Not Broadcasting

1. **Check default broadcaster**:
   ```bash
   php artisan config:show broadcasting.default
   ```
   Should be `reverb`.

2. **Verify event implements ShouldBroadcastNow** (for immediate broadcasting):
   ```php
   class MyEvent implements ShouldBroadcastNow
   ```
   Or `ShouldBroadcast` with `ShouldQueue` for queued broadcasting.

3. **Check Reverb server logs**:
   ```bash
   php artisan reverb:start --debug
   ```

4. **Test broadcasting with the example command**:
   ```bash
   php artisan reverb:broadcast "Test message"
   ```

### Private Channels Not Working

1. **Verify authentication**:
   - User must be authenticated
   - Check `routes/channels.php` authorization logic

2. **Check auth endpoint**:
   - Should be accessible at `/api/broadcasting/auth`
   - Must return proper authorization response

3. **Verify credentials**:
   - `withCredentials: true` must be set in Echo config (already configured)

### Performance Issues

1. **Queue heavy broadcasts**:
   ```php
   class HeavyEvent implements ShouldBroadcast, ShouldQueue
   ```

2. **Limit broadcast data**:
   - Only broadcast necessary data
   - Use `broadcastWith()` to control what's sent

3. **Use specific channels**:
   - Avoid broadcasting to too many channels
   - Use private channels to target specific users

## Additional Resources

- [Laravel Reverb Documentation](https://laravel.com/docs/12.x/reverb)
- [Laravel Broadcasting Documentation](https://laravel.com/docs/12.x/broadcasting)
- [Laravel Echo Documentation](https://laravel.com/docs/12.x/broadcasting#client-side-installation)

## Example Files

- **Server Event**: `app/Events/ExampleBroadcastEvent.php`
- **Client Component**: `client/src/components/event-listeners/example-reverb-listener.vue`
- **Echo Composable**: `client/src/composables/use-echo.ts`
- **Channel Routes**: `routes/channels.php`
- **Broadcast Command**: `routes/console.php` (see `reverb:broadcast` command)

## Testing Your Setup

### Quick Test

1. **Start the Reverb server**:
   ```bash
   php artisan reverb:start --debug
   ```

2. **Open your client application** in a browser

3. **Broadcast a test event**:
   ```bash
   php artisan reverb:broadcast "Hello from Reverb!"
   ```

4. **Check the browser console** - you should see the message appear in real-time

### Example Component

The included example component (`client/src/components/event-listeners/example-reverb-listener.vue`) demonstrates:
- Subscribing to a public channel
- Listening for specific events
- Displaying received messages
- Proper cleanup on component unmount

