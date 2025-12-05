<script setup lang="ts">
import { toast } from 'vue-sonner'

import type { User } from '@/services/users.service'

interface UserDeleteProps {
  user: User
}

const props = defineProps<UserDeleteProps>()

function handleRemove() {
  toast(`The following user has been deleted:`, {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(props.user, null, 2)),
    ),
  })
}
</script>

<template>
  <div>
    <UiDialogTitle> Delete this user: {{ user.name }} ? </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a user with the ID {{ user.id }}. This action cannot be undone.
    </UiDialogDescription>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>
      <UiDialogClose as-child>
        <UiButton variant="destructive" @click="handleRemove"> Delete </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>
