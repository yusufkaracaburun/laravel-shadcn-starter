<script setup lang="ts">
import { BadgeCheck } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

interface Props {
  user: IUser
  name: string
}

const props = defineProps<Props>()
const router = useRouter()

// Get initials from name
function getInitials(name: string): string {
  if (!name || name === '—')
    return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
}

// Navigate to user detail page
function handleClick() {
  router.push({ name: '/users/[id]', params: { id: props.user.id.toString() } })
}
</script>

<template>
  <div class="flex items-center gap-3">
    <Avatar class="size-8">
      <AvatarImage
        v-if="user.profile_photo_url"
        :src="user.profile_photo_url"
        :alt="name"
      />
      <AvatarFallback>
        {{ getInitials(name) }}
      </AvatarFallback>
    </Avatar>
    <div class="flex items-center gap-1.5">
      <button
        class="font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline"
        @click="handleClick"
      >
        {{ name }}
      </button>
      <BadgeCheck
        v-if="user.email_verified_at"
        class="size-4 text-primary flex-shrink-0"
        aria-label="Verified"
      />
    </div>
  </div>
</template>
