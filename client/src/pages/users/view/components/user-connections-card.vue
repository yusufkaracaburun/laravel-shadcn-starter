<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { ChevronRight } from 'lucide-vue-next'

// Mock data - in real app, this would come from API
const connections = [
  {
    id: 1,
    name: 'Olivia Davis',
    email: 'olivia.davis@example.com',
    avatar: null,
    isConnected: false,
  },
  {
    id: 2,
    name: 'John Doe',
    email: 'john.doe@example.com',
    avatar: null,
    isConnected: true,
  },
  {
    id: 3,
    name: 'Alice Smith',
    email: 'alice.smith@example.com',
    avatar: null,
    isConnected: false,
  },
  {
    id: 4,
    name: 'Emily Martinez',
    email: 'emily.martinez@example.com',
    avatar: null,
    isConnected: true,
  },
  {
    id: 5,
    name: 'James Wilson',
    email: 'james.wilson@example.com',
    avatar: null,
    isConnected: true,
  },
]

function getInitials(name: string) {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
}
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <CardTitle>Connections</CardTitle>
        <Button variant="ghost" size="icon" class="size-8">
          <ChevronRight class="size-4" />
        </Button>
      </div>
    </CardHeader>
    <CardContent class="space-y-4">
      <div
        v-for="connection in connections"
        :key="connection.id"
        class="flex items-center justify-between"
      >
        <div class="flex items-center gap-3">
          <Avatar class="size-10">
            <AvatarImage
              v-if="connection.avatar"
              :src="connection.avatar"
              :alt="connection.name"
            />
            <AvatarFallback class="text-sm">
              {{ getInitials(connection.name) }}
            </AvatarFallback>
          </Avatar>
          <div>
            <p class="font-medium text-sm">
              {{ connection.name }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ connection.email }}
            </p>
          </div>
        </div>
        <Button
          :variant="connection.isConnected ? 'outline' : 'default'"
          size="sm"
        >
          {{ connection.isConnected ? 'Disconnect' : 'Connect' }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
