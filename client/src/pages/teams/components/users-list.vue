<script setup lang="ts">
import { Search, User } from 'lucide-vue-next'
import Draggable from 'vuedraggable'

import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'

interface Props {
  users: IUser[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const searchQuery = ref('')

const filteredUsers = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.users
  }

  const query = searchQuery.value.toLowerCase().trim()
  return props.users.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query),
  )
})

function getInitials(name: string): string {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Users</h2>
      <Badge variant="secondary"> {{ filteredUsers.length }} users </Badge>
    </div>

    <div class="relative">
      <Search
        class="absolute left-2 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
      />
      <Input
        v-model="searchQuery"
        placeholder="Search users by name or email..."
        class="h-8 pl-8"
      />
    </div>

    <div v-if="loading && users.length === 0" class="space-y-2">
      <Card v-for="i in 5" :key="i" class="animate-pulse">
        <CardContent class="flex items-center gap-1.5 px-1.5 py-0">
          <div class="size-7 rounded-full bg-muted" />
          <div class="flex-1 space-y-1">
            <div class="h-2.5 bg-muted rounded w-1/3" />
            <div class="h-2 bg-muted rounded w-1/2" />
          </div>
        </CardContent>
      </Card>
    </div>

    <div v-else-if="filteredUsers.length === 0" class="text-center py-12">
      <User class="mx-auto size-12 text-muted-foreground mb-4" />
      <p class="text-muted-foreground">
        {{ searchQuery ? 'No users match your search' : 'No users found' }}
      </p>
    </div>

    <Draggable
      v-else
      :model-value="filteredUsers"
      :group="{ name: 'team-users', pull: 'clone', put: false }"
      item-key="id"
      :sort="false"
      :clone="
        (user: IUser) => ({ id: user.id, name: user.name, email: user.email })
      "
      ghost-class="opacity-50"
      class="space-y-2 max-h-[calc(100vh-300px)] overflow-y-auto"
    >
      <template #item="{ element: user }: { element: IUser }">
        <Card
          class="cursor-grab active:cursor-grabbing hover:bg-accent/50 transition-colors p-3"
        >
          <CardContent class="flex items-center gap-3 px-2">
            <Avatar class="size-7 shrink-0">
              <AvatarImage
                v-if="user.profile_photo_url"
                :src="user.profile_photo_url"
                :alt="user.name"
              />
              <AvatarFallback class="text-[10px]">
                {{ getInitials(user.name) }}
              </AvatarFallback>
            </Avatar>

            <div class="flex-1 min-w-0">
              <div class="font-medium text-xs truncate leading-tight">
                {{ user.name }}
              </div>
              <div
                class="text-[10px] text-muted-foreground truncate leading-tight"
              >
                {{ user.email }}
              </div>
            </div>

            <div class="flex items-center gap-1 shrink-0 flex-wrap">
              <Badge
                v-for="role in user.roles"
                :key="role.id"
                variant="outline"
                class="text-[9px] px-1 py-0 capitalize"
              >
                {{ role.name }}
              </Badge>
              <Badge
                v-if="user.status"
                :variant="user.status === 'active' ? 'default' : 'secondary'"
                class="text-[9px] px-1 py-0 capitalize"
              >
                {{ user.status }}
              </Badge>
            </div>
          </CardContent>
        </Card>
      </template>
    </Draggable>
  </div>
</template>
