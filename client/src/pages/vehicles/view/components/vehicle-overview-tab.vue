<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import DetailsField from '@/components/global-layout/components/details-page/details-field.vue'
import DetailsSection from '@/components/global-layout/components/details-page/details-section.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  CalendarIcon,
  CircleIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { formatDate } from '@/utils/date'

import { useVehicleStatus } from '@/pages/vehicles/composables/use-vehicle-status.composable'

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

const driversCount = computed(() => {
  return props.vehicle.drivers?.length ?? 0
})
</script>

<template>
  <div class="space-y-3">
    <!-- Quick Stats -->
    <div class="grid gap-3 md:grid-cols-2">
      <div class="group relative overflow-hidden rounded-lg border bg-card p-4 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="rounded-md bg-primary/10 p-2">
              <CircleIcon class="size-4 text-primary" />
            </div>
            <div>
              <div class="text-xs font-medium text-muted-foreground">
                Status
              </div>
              <div class="mt-0.5">
                <!-- TODO: Implement inline status change
                    - Add click handler to status badge
                    - Open DropdownMenu with status options from statuses data (active, inactive, maintenance)
                    - Call updateVehicle mutation with new status
                    - Show loading state during update
                    - Handle errors appropriately
                    - Refresh vehicle data after successful update
                -->
                <Badge
                  v-if="vehicle.status"
                  :variant="getStatusVariant(vehicle.status)"
                  :class="getStatusInfo(vehicle.status)?.color || ''"
                >
                  {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
                </Badge>
                <span v-else class="text-sm font-medium">Onbekend</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="group relative overflow-hidden rounded-lg border bg-card p-4 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="rounded-md bg-blue-500/10 p-2">
              <UsersIcon class="size-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <div class="text-xs font-medium text-muted-foreground">
                Bestuurders
              </div>
              <div class="mt-0.5 text-2xl font-bold tracking-tight">
                {{ driversCount }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Vehicle Summary -->
    <DetailsSection title="Voertuiginformatie">
      <div class="grid gap-3 md:grid-cols-2">
        <DetailsField
          label="Kenteken"
          :value="vehicle.license_plate"
        />
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
        />
      </div>
    </DetailsSection>

    <!-- Inspection Status -->
    <DetailsSection title="APK-status">
      <div class="space-y-2">
        <DetailsField
          v-if="formattedInspectionDate"
          label="APK-datum"
          :value="formattedInspectionDate"
        />
        <div v-if="daysToInspectionText">
          <DetailsField
            label="Dagen tot APK"
            :value="daysToInspectionText"
          />
          <Progress :model-value="inspectionProgressValue" class="h-2 mt-1" />
        </div>
        <div v-else class="text-sm text-muted-foreground">
          Geen APK-datum ingesteld
        </div>
      </div>
    </DetailsSection>

    <!-- Timestamps -->
    <DetailsSection title="Systeeminformatie">
      <div class="grid gap-3 md:grid-cols-2">
        <DetailsField
          label="Aangemaakt"
          :value="formattedCreatedAt"
        />
        <DetailsField
          label="Bijgewerkt"
          :value="formattedUpdatedAt"
        />
      </div>
    </DetailsSection>
  </div>
</template>