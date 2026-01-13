<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { MailIcon } from '@/composables/use-icons.composable'

interface Props {
  user: IUser
  initials: string
  isEmailVerified: boolean
}

defineProps<Props>()
</script>

<template>
  <div class="flex items-start gap-6">
    <Avatar class="size-20 ring-4 ring-background shadow-lg">
      <AvatarImage
        v-if="user.profile_photo_url"
        :src="user.profile_photo_url"
        :alt="user.name"
      />
      <AvatarFallback
        class="text-2xl font-semibold bg-primary text-primary-foreground"
      >
        {{ initials }}
      </AvatarFallback>
    </Avatar>
    <div class="flex-1 min-w-0">
      <h1 class="text-3xl font-bold tracking-tight mb-2">
        {{ user.name }}
      </h1>
      <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center gap-2 text-muted-foreground">
          <MailIcon class="size-4" />
          <span class="text-base">{{ user.email }}</span>
        </div>
        <Badge
          :variant="isEmailVerified ? 'default' : 'secondary'"
          class="shrink-0"
        >
          {{ isEmailVerified ? 'Verified' : 'Unverified' }}
        </Badge>
      </div>
    </div>
  </div>
</template>
