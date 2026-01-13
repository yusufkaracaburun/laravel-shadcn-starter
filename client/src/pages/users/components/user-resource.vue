<script lang="ts" setup>
import type { IUser } from '@/pages/users/models/users'

import {
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'
import UserForm from './user-form.vue'

interface IUserResourceProps {
  user?: IUser | null
}
const props = defineProps<IUserResourceProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.user ? 'Edit User' : 'Create New User'))
const description = computed(() =>
  props.user
    ? `Update user information for ${props.user.name}. Leave password fields blank to keep the current password.`
    : 'Add a new user to the system. Fill in the required information below.',
)
</script>

<template>
  <SheetHeader>
    <SheetTitle>
      {{ title }}
    </SheetTitle>
    <SheetDescription>
      {{ description }}
    </SheetDescription>
  </SheetHeader>

  <UserForm :user="user" class="mt-4" @close="emits('close')" />
</template>
