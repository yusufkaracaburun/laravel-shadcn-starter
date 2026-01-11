<script lang="ts" setup>
import type { IEquipment } from '@/pages/equipments/models/equipments'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import EquipmentsResourceDialog from './equipments-resource-dialog.vue'

interface IEquipmentEditDialogProps {
  equipment: IEquipment
  open: boolean
}

const props = defineProps<IEquipmentEditDialogProps>()

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
      <EquipmentsResourceDialog :equipment="props.equipment" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
