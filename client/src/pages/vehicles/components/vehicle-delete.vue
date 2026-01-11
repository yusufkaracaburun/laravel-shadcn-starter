<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

interface IVehicleDeleteProps {
  vehicle: IVehicle
}

const props = defineProps<IVehicleDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteVehicle } = useVehicles()

async function handleRemove() {
  await deleteVehicle(props.vehicle.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle>
      Delete this vehicle: {{ vehicle.license_plate }}?
    </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a vehicle with the ID {{ vehicle.id }}. This
      action cannot be undone.
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
