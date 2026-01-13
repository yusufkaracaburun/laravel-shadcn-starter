<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { useUserService } from '@/pages/users/services/users.service'

interface Props {
  userId: number | null
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'update:userId': [value: number | null]
}>()

const isOpen = computed({
  get: () => props.open,
  set: (value) => {
    emit('update:open', value)
    if (!value) {
      emit('update:userId', null)
    }
  },
})

const userService = useUserService()
const userIdRef = computed(() => props.userId ?? undefined)

const getUserByIdQuery = userService.getUserByIdQuery(
  userIdRef,
  ref(['roles', 'teams']),
)

const { data: userByIdResponse } = getUserByIdQuery

const selectedUser = computed<IUser | null>(() => {
  if (!props.userId) {
    return null
  }
  const data = userByIdResponse.value?.data
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
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
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>User Details</DialogTitle>
        <DialogDescription> View user information </DialogDescription>
      </DialogHeader>
      <div v-if="selectedUser" class="space-y-4 py-4">
        <div class="flex items-center gap-4">
          <Avatar class="size-16">
            <AvatarImage
              v-if="selectedUser.profile_photo_url"
              :src="selectedUser.profile_photo_url"
              :alt="selectedUser.name"
            />
            <AvatarFallback class="text-lg">
              {{ getInitials(selectedUser.name) }}
            </AvatarFallback>
          </Avatar>
          <div>
            <h3 class="text-lg font-semibold">
              {{ selectedUser.name }}
            </h3>
            <p class="text-sm text-muted-foreground">
              {{ selectedUser.email }}
            </p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-muted-foreground mb-1">Roles</div>
            <div
              v-if="selectedUser.roles && selectedUser.roles.length > 0"
              class="flex flex-wrap gap-1"
            >
              <Badge
                v-for="role in selectedUser.roles"
                :key="role.id"
                variant="outline"
                class="capitalize"
              >
                {{ role.name }}
              </Badge>
            </div>
            <div v-else class="font-medium text-muted-foreground">
              No roles assigned
            </div>
          </div>
          <div>
            <div class="text-muted-foreground mb-1">Status</div>
            <Badge
              v-if="selectedUser.status"
              :variant="
                selectedUser.status === 'active' ? 'default' : 'secondary'
              "
              class="capitalize"
            >
              {{ selectedUser.status }}
            </Badge>
            <div v-else class="font-medium text-muted-foreground">
              Not set
            </div>
          </div>
          <div v-if="selectedUser.email_verified_at">
            <div class="text-muted-foreground">Email Verified</div>
            <div class="font-medium">Yes</div>
          </div>
          <div>
            <div class="text-muted-foreground">Member Since</div>
            <div class="font-medium">
              {{ new Date(selectedUser.created_at).toLocaleDateString() }}
            </div>
          </div>
        </div>
      </div>
      <div v-else class="py-8 text-center text-muted-foreground">
        Loading user details...
      </div>
    </DialogContent>
  </Dialog>
</template>
