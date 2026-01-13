<script lang="ts" setup>
import type { IUser } from '@/pages/users/models/users'

import UserForm from './user-form.vue'

interface IUserResourceDialogProps {
  user: IUser | null
}

const props = defineProps<IUserResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

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
    <UserForm :user="user" class="mt-2" @close="emits('close')" />
  </div>
</template>
