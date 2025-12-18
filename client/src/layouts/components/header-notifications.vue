<script setup lang="ts">
import { Bell } from 'lucide-vue-next'
import { ref } from 'vue'

// Notification state
export interface Notification {
  id: number
  title: string
  message: string
  time: string
  read: boolean
  type: string
}

const notificationOpen = ref(false)
const unreadCount = ref(3) // Mock unread count

// Mock notifications data
const notifications = ref<Notification[]>([
  {
    id: 1,
    title: 'New message received',
    message: 'You have a new message from John Doe',
    time: '2 minutes ago',
    read: false,
    type: 'message',
  },
  {
    id: 2,
    title: 'Task assigned',
    message: 'A new task has been assigned to you',
    time: '1 hour ago',
    read: false,
    type: 'task',
  },
  {
    id: 3,
    title: 'System update',
    message: 'Your system has been updated successfully',
    time: '3 hours ago',
    read: false,
    type: 'system',
  },
  {
    id: 4,
    title: 'Payment received',
    message: 'Payment of $500 has been received',
    time: '1 day ago',
    read: true,
    type: 'payment',
  },
])

// Notification handlers
function markAsRead(id: number) {
  const notification = notifications.value.find((n) => n.id === id)
  if (notification && !notification.read) {
    notification.read = true
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  }
}

function markAllAsRead() {
  notifications.value.forEach((notification) => {
    if (!notification.read) {
      notification.read = true
    }
  })
  unreadCount.value = 0
}
</script>

<template>
  <UiDropdownMenu v-model:open="notificationOpen">
    <UiDropdownMenuTrigger as-child>
      <UiButton
        variant="ghost"
        size="icon"
        class="relative"
        data-testid="default-layout_notifications-button"
      >
        <Bell class="size-4" />
        <UiBadge
          v-if="unreadCount > 0"
          variant="destructive"
          class="absolute -top-1 -right-1 h-5 min-w-5 px-1 text-[10px] flex items-center justify-center"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </UiBadge>
      </UiButton>
    </UiDropdownMenuTrigger>
    <UiDropdownMenuContent class="w-80 rounded-lg" align="end" :side-offset="4">
      <UiDropdownMenuLabel class="flex items-center justify-between px-3 py-2">
        <span class="font-semibold">Notifications</span>
        <UiButton
          v-if="unreadCount > 0"
          variant="ghost"
          size="sm"
          class="h-7 text-xs"
          @click.stop="markAllAsRead"
        >
          Mark all as read
        </UiButton>
      </UiDropdownMenuLabel>
      <UiDropdownMenuSeparator />
      <div class="max-h-[400px] overflow-y-auto">
        <UiDropdownMenuItem
          v-for="notification in notifications"
          :key="notification.id"
          class="flex flex-col items-start gap-1 px-3 py-2 cursor-pointer"
          :class="!notification.read && 'bg-muted/50'"
          @click="markAsRead(notification.id)"
        >
          <div class="flex items-start justify-between w-full gap-2">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate">
                {{ notification.title }}
              </p>
              <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                {{ notification.message }}
              </p>
              <p class="text-xs text-muted-foreground mt-1">
                {{ notification.time }}
              </p>
            </div>
            <div v-if="!notification.read" class="h-2 w-2 rounded-full bg-primary shrink-0 mt-1" />
          </div>
        </UiDropdownMenuItem>
        <UiDropdownMenuItem
          v-if="notifications.length === 0"
          class="flex items-center justify-center py-8 text-muted-foreground"
        >
          No notifications
        </UiDropdownMenuItem>
      </div>
      <UiDropdownMenuSeparator />
      <UiDropdownMenuItem
        class="justify-center cursor-pointer"
        @click="$router.push('/settings/notifications')"
      >
        View all notifications
      </UiDropdownMenuItem>
    </UiDropdownMenuContent>
  </UiDropdownMenu>
</template>
