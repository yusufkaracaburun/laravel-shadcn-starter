<script lang="ts" setup>
import type { IUser } from '@/pages/users/models/users'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import UserResourceDialog from './user-resource-dialog.vue'

interface IUserEditDialogProps {
  user: IUser
  open: boolean
}

const props = defineProps<IUserEditDialogProps>()

const emits = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'close'): void
}>()

function handleClose() {
  emits('update:open', false)
  emits('close')
}
</script>

<template>
  <Dialog :open="props.open" @update:open="emits('update:open', $event)">
    <DialogContent
      class="top-10 right-10 bottom-10 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <UserResourceDialog :user="props.user" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
