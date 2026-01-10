<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { MailIcon } from '@/composables/use-icons.composable'

interface Props {
  user: IUser
  initials: string
  isEmailVerified: boolean
}

defineProps<Props>()
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-start gap-6">
        <Avatar class="size-24">
          <AvatarImage
            v-if="user.profile_photo_url"
            :src="user.profile_photo_url"
            :alt="user.name"
          />
          <AvatarFallback class="text-2xl">
            {{ initials }}
          </AvatarFallback>
        </Avatar>
        <div class="flex-1">
          <CardTitle class="text-3xl mb-2">
            {{ user.name }}
          </CardTitle>
          <CardDescription class="text-base flex items-center gap-2">
            <MailIcon class="size-4" />
            {{ user.email }}
          </CardDescription>
          <div class="mt-4 flex items-center gap-2">
            <Badge :variant="isEmailVerified ? 'default' : 'secondary'">
              {{ isEmailVerified ? 'Verified' : 'Unverified' }}
            </Badge>
          </div>
        </div>
      </div>
    </CardHeader>
  </Card>
</template>
