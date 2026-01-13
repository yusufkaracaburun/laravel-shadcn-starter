<script lang="ts" setup>
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import VehicleForm from './vehicle-form.vue'

interface IVehicleResourceDialogProps {
  vehicle: IVehicle | null
}

const props = defineProps<IVehicleResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.vehicle ? 'Edit Vehicle' : 'New Vehicle'))
const description = computed(() =>
  props.vehicle
    ? `Update vehicle information for ${props.vehicle.license_plate}.`
    : 'Add a new vehicle to the system. Fill in the required information below.',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <VehicleForm :vehicle="vehicle" class="mt-2" @close="emits('close')" />
  </div>
</template>
