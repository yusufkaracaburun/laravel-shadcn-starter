<script lang="ts" setup>
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import VehicleResourceDialog from './vehicle-resource-dialog.vue'

interface IVehicleEditDialogProps {
  vehicle: IVehicle
  open: boolean
}

const props = defineProps<IVehicleEditDialogProps>()

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
      <VehicleResourceDialog :vehicle="props.vehicle" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
