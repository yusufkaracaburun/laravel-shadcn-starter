<script lang="ts" setup>
import type { IItem } from '@/pages/items/models/items'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import ItemResourceDialog from './item-resource-dialog.vue'

interface IItemEditDialogProps {
  item: IItem
  open: boolean
}

const props = defineProps<IItemEditDialogProps>()

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
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <ItemResourceDialog :item="props.item" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
