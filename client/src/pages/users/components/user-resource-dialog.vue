<script lang="ts" setup>
import type { User } from '@/services/users.service'

import UserForm from './user-form.vue'

const props = defineProps<{
  user?: User | null
}>()
defineEmits(['close'])

const title = computed(() => (props.user ? 'Edit User' : 'New User'))
const description = computed(() =>
  props.user
    ? `Update user information for ${props.user.name}. Leave password fields blank to keep the current password.`
    : 'Add a new user to the system. Fill in the required information below.',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <UserForm :user="user" class="mt-2" @close="$emit('close')" />
  </div>
</template>
