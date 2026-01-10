<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import { useUsers } from '@/pages/users/composables/use-users.composable'

interface IUserDeleteProps {
  user: IUser
}

const props = defineProps<IUserDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteUser } = useUsers()

async function handleRemove() {
  await deleteUser(props.user.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle> Delete this user: {{ user.name }} ? </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a user with the ID {{ user.id }}. This action
      cannot be undone.
    </UiDialogDescription>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>
      <UiDialogClose as-child>
        <UiButton variant="destructive" @click="handleRemove">
          Delete
        </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>
