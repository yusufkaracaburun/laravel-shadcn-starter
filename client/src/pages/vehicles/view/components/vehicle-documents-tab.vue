<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import VehicleDocumentsSection from '@/pages/vehicles/components/vehicle-documents-section.vue'

interface Props {
  vehicle: IVehicle
}

const props = defineProps<Props>()
</script>

<template>
  <div class="space-y-3">
    <VehicleDocumentsSection />
  </div>
</template>
