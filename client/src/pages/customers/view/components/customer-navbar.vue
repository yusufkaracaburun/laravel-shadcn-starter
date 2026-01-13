<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { ICustomer } from '@/pages/customers/models/customers'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  ArrowLeftIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import CustomerDelete from '@/pages/customers/components/customer-delete.vue'
import CustomerEditDialog from '@/pages/customers/components/customer-edit-dialog.vue'

const props = defineProps<{
  customer: ICustomer
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const router = useRouter()

const showComponent = shallowRef<typeof CustomerDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close' | 'back'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      showComponent.value = null
      break
    case 'back':
      router.push('/customers')
      break
  }
}

function handleEditClose() {
  isEditDialogOpen.value = false
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

  <CustomerEditDialog
    v-if="props.customer"
    :customer="props.customer"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="handleEditClose"
  />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent
      v-if="showComponent && props.customer"
      class="sm:max-w-[425px]"
    >
      <CustomerDelete
        v-if="showComponent === CustomerDelete"
        :customer="props.customer"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
