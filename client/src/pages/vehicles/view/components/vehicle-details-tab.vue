<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import DetailsField from '@/components/global-layout/components/details-page/details-field.vue'
import DetailsSection from '@/components/global-layout/components/details-page/details-section.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  CalendarIcon,
} from '@/composables/use-icons.composable'
import { useVehicleStatus } from '@/pages/vehicles/composables/use-vehicle-status.composable'
import { formatDate } from '@/utils/date'

interface Props {
  vehicle: IVehicle
}

const props = defineProps<Props>()

const { getStatusInfo, getStatusVariant } = useVehicleStatus()

const formattedCreatedAt = computed(() => formatDate(props.vehicle.created_at))
const formattedUpdatedAt = computed(() => formatDate(props.vehicle.updated_at))
const formattedInspectionDate = computed(() => {
  if (props.vehicle.inspection_date) {
    return formatDate(props.vehicle.inspection_date)
  }
  return null
})

// Inspection progress - days to inspection (assuming max 365 days = 100%)
const inspectionProgressValue = computed(() => {
  if (!props.vehicle.days_to_inspection) {
    return 0
  }
  return Math.max(
    0,
    Math.min(100, ((365 - props.vehicle.days_to_inspection) / 365) * 100),
  )
})

const daysToInspectionText = computed(() => {
  if (
    props.vehicle.days_to_inspection === null ||
    props.vehicle.days_to_inspection === undefined
  ) {
    return null
  }
  return `${props.vehicle.days_to_inspection} dagen te gaan`
})
</script>

<template>
  <div class="space-y-2">
    <!-- Basic Information -->
    <DetailsSection title="Basisinformatie">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField label="Kenteken" :value="vehicle.license_plate" />
        <DetailsField label="Status">
          <!-- TODO: Implement inline status change -->
          <Badge
            v-if="vehicle.status"
            :variant="getStatusVariant(vehicle.status)"
            :class="getStatusInfo(vehicle.status)?.color || ''"
          >
            {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
          </Badge>
          <span v-else class="text-sm font-medium">Onbekend</span>
        </DetailsField>
        <DetailsField
          v-if="vehicle.make"
          label="Merk"
          :value="vehicle.make"
        />
        <DetailsField
          v-if="vehicle.model"
          label="Model"
          :value="vehicle.model"
        />
        <DetailsField
          v-if="vehicle.year"
          label="Jaar"
          :value="vehicle.year"
        />
        <DetailsField
          v-if="vehicle.color"
          label="Kleur"
          :value="vehicle.color"
        />
        <DetailsField
          v-if="vehicle.vin"
          label="VIN"
          :value="vehicle.vin"
          :span="2"
        />
      </div>
    </DetailsSection>

    <!-- Inspection Information -->
    <DetailsSection title="APK-informatie">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField
          v-if="formattedInspectionDate"
          label="APK-datum"
          :value="formattedInspectionDate"
          :icon="CalendarIcon"
        />
        <div v-if="daysToInspectionText">
          <DetailsField
            label="Dagen tot APK"
            :value="daysToInspectionText"
          />
          <Progress :model-value="inspectionProgressValue" class="h-2 mt-1" />
        </div>
        <div
          v-if="!formattedInspectionDate && !daysToInspectionText"
          class="md:col-span-2 text-sm text-muted-foreground"
        >
          Geen APK-datum ingesteld
        </div>
      </div>
    </DetailsSection>

    <!-- System Information -->
    <DetailsSection title="Systeeminformatie">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField
          label="Aangemaakt op"
          :value="formattedCreatedAt"
          :icon="CalendarIcon"
        />
        <DetailsField
          label="Bijgewerkt op"
          :value="formattedUpdatedAt"
          :icon="CalendarIcon"
        />
        <DetailsField label="Voertuig ID" :value="`#${vehicle.id}`" />
      </div>
    </DetailsSection>
  </div>
</template>
