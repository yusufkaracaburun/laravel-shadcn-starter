<script lang="ts" setup>
import { Badge } from '@/components/ui/badge'

interface Notification {
  id: string
  title: string
  description: string
  timestamp: string
  read: boolean
  type: 'info' | 'success' | 'warning' | 'error'
}

const notifications = ref<Notification[]>([
  {
    id: '1',
    title: 'New order received',
    description: 'Order #12345 has been placed by John Doe',
    timestamp: '2024-06-15T10:30:00',
    read: false,
    type: 'success',
  },
  {
    id: '2',
    title: 'System maintenance scheduled',
    description: 'Scheduled maintenance on June 20, 2024 from 2:00 AM to 4:00 AM',
    timestamp: '2024-06-15T09:15:00',
    read: false,
    type: 'info',
  },
  {
    id: '3',
    title: 'Payment processed',
    description: 'Payment of $1,234.56 has been successfully processed',
    timestamp: '2024-06-14T16:45:00',
    read: true,
    type: 'success',
  },
  {
    id: '4',
    title: 'Low inventory alert',
    description: 'Product "Widget A" is running low on stock (5 units remaining)',
    timestamp: '2024-06-14T14:20:00',
    read: false,
    type: 'warning',
  },
  {
    id: '5',
    title: 'Failed payment attempt',
    description: 'Payment attempt failed for order #12340. Please review.',
    timestamp: '2024-06-14T11:00:00',
    read: true,
    type: 'error',
  },
  {
    id: '6',
    title: 'New user registered',
    description: 'A new user "jane.doe@example.com" has registered',
    timestamp: '2024-06-13T15:30:00',
    read: true,
    type: 'info',
  },
  {
    id: '7',
    title: 'Report generated',
    description: 'Monthly sales report has been generated and is ready for download',
    timestamp: '2024-06-13T08:00:00',
    read: true,
    type: 'success',
  },
])

const formatTimestamp = (timestamp: string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))

  if (diffInHours < 1) {
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
    return `${diffInMinutes} minutes ago`
  } else if (diffInHours < 24) {
    return `${diffInHours} hours ago`
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays} days ago`
  }
}

const getTypeColor = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'warning':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'error':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'info':
    default:
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
  }
}

const markAsRead = (notification: Notification) => {
  notification.read = true
}
</script>

<template>
  <UiCard data-testid="notifications-content_card">
    <UiCardHeader>
      <UiCardTitle>Notifications</UiCardTitle>
      <UiCardDescription> Stay updated with your latest notifications </UiCardDescription>
    </UiCardHeader>
    <UiCardContent>
      <div class="space-y-4" data-testid="notifications-content_list">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="flex items-start gap-4 rounded-lg border p-4 transition-colors hover:bg-accent"
          :class="{ 'bg-muted/50': !notification.read }"
          data-testid="notifications-content_notification-item"
        >
          <div class="flex-1 space-y-1">
            <div class="flex items-center gap-2">
              <h4 class="font-semibold">{{ notification.title }}</h4>
              <Badge :class="getTypeColor(notification.type)" class="text-xs">
                {{ notification.type }}
              </Badge>
              <Badge
                v-if="!notification.read"
                variant="default"
                class="text-xs"
                data-testid="notifications-content_unread-badge"
              >
                New
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground">{{ notification.description }}</p>
            <p class="text-xs text-muted-foreground">
              {{ formatTimestamp(notification.timestamp) }}
            </p>
          </div>
          <Button
            v-if="!notification.read"
            variant="ghost"
            size="sm"
            @click="markAsRead(notification)"
            data-testid="notifications-content_mark-read-button"
          >
            Mark as read
          </Button>
        </div>
      </div>
    </UiCardContent>
  </UiCard>
</template>
