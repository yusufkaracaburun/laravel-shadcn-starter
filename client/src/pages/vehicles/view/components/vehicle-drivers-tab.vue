<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import VehicleDriversSection from '@/pages/vehicles/components/vehicle-drivers-section.vue'

interface Props {
  vehicle: IVehicle
}

const props = defineProps<Props>()
</script>

<template>
  <div class="space-y-3">
    <VehicleDriversSection :vehicle="props.vehicle" />
  </div>
</template>
