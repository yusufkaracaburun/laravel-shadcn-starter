<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { IUser } from '@/pages/users/models/users'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  ArrowLeftIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import UserDelete from '@/pages/users/components/user-delete.vue'
import UserResourceDialog from '@/pages/users/components/user-resource-dialog.vue'

const props = defineProps<{
  user: IUser
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const router = useRouter()

const showComponent = shallowRef<typeof UserDelete | typeof UserResourceDialog | null>(null)
const isDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close' | 'back'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      showComponent.value = UserResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = UserDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      showComponent.value = null
      break
    case 'back':
      router.push('/users')
      break
  }
}

function handleEditClose() {
  handleSelect('close')
  emits('edit-closed')
}

function handleDeleteClose() {
  handleSelect('close')
  emits('delete-closed')
}
</script>

<template>
  <div class="flex items-center gap-2">
    <Button variant="outline" @click="handleSelect('back')">
      <ArrowLeftIcon class="mr-2 size-4" />
      Back
    </Button>
    <Button variant="outline" @click="handleSelect('edit')">
      <FilePenLineIcon class="mr-2 size-4" />
      Edit
    </Button>
    <Button variant="destructive" @click="handleSelect('delete')">
      <Trash2Icon class="mr-2 size-4" />
      Delete
    </Button>
  </div>

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent
      v-if="showComponent && props.user"
      class="sm:max-w-[425px]"
    >
      <UserResourceDialog
        v-if="showComponent === UserResourceDialog"
        :user="props.user"
        @close="handleEditClose"
      />
      <UserDelete
        v-else-if="showComponent === UserDelete"
        :user="props.user"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
