<script lang="ts" setup>
import type { User } from '@/services/users.service'

import UserForm from './user-form.vue'

const props = defineProps<{
  user?: User | null
}>()

defineEmits(['close'])

const title = computed(() => (props.user ? 'Edit User' : 'Create New User'))
const description = computed(() =>
  props.user
    ? `Update user information for ${props.user.name}. Leave password fields blank to keep the current password.`
    : 'Add a new user to the system. Fill in the required information below.',
)
</script>

<template>
  <UiDrawerHeader>
    <UiDrawerTitle>
      {{ title }}
    </UiDrawerTitle>
    <UiDrawerDescription>
      {{ description }}
    </UiDrawerDescription>
  </UiDrawerHeader>

  <UserForm :user="user" class="mt-4" @close="$emit('close')" />
</template>
