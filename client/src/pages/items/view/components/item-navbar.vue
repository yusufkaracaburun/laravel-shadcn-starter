<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { IItem } from '@/pages/items/models/items'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  ArrowLeftIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import ItemDelete from '@/pages/items/components/item-delete.vue'
import ItemEditDialog from '@/pages/items/components/item-edit-dialog.vue'

const props = defineProps<{
  item: IItem
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const router = useRouter()

const showComponent = shallowRef<typeof ItemDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close' | 'back'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = ItemDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      showComponent.value = null
      break
    case 'back':
      router.push('/items')
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

  <ItemEditDialog
    v-if="props.item"
    :item="props.item"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="handleEditClose"
  />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent v-if="showComponent && props.item" class="sm:max-w-[425px]">
      <ItemDelete
        v-if="showComponent === ItemDelete"
        :item="props.item"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
