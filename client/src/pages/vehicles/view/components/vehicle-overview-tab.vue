<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  BoxIcon,
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
    <div class="rounded-lg border bg-card p-4 shadow-sm">
      <div class="text-sm font-semibold mb-4">
        Voertuiginformatie
      </div>
      <div class="grid gap-3 md:grid-cols-2">
        <div>
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Kenteken
          </div>
          <div class="text-sm font-medium">
            {{ vehicle.license_plate }}
          </div>
        </div>
        <div v-if="vehicle.make">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Merk
          </div>
          <div class="text-sm">
            {{ vehicle.make }}
          </div>
        </div>
        <div v-if="vehicle.model">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Model
          </div>
          <div class="text-sm">
            {{ vehicle.model }}
          </div>
        </div>
        <div v-if="vehicle.year">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Jaar
          </div>
          <div class="text-sm">
            {{ vehicle.year }}
          </div>
        </div>
        <div v-if="vehicle.color">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Kleur
          </div>
          <div class="text-sm">
            {{ vehicle.color }}
          </div>
        </div>
        <div v-if="vehicle.vin">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            VIN
          </div>
          <div class="text-sm">
            {{ vehicle.vin }}
          </div>
        </div>
      </div>
    </div>

    <!-- Inspection Status -->
    <div class="rounded-lg border bg-card p-4 shadow-sm">
      <div class="text-sm font-semibold mb-4">
        APK-status
      </div>
      <div class="space-y-2">
        <div v-if="formattedInspectionDate">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            APK-datum
          </div>
          <div class="text-sm">
            {{ formattedInspectionDate }}
          </div>
        </div>
        <div v-if="daysToInspectionText">
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Dagen tot APK
          </div>
          <div class="text-sm mb-1">
            {{ daysToInspectionText }}
          </div>
          <Progress :model-value="inspectionProgressValue" class="h-2" />
        </div>
        <div v-else class="text-sm text-muted-foreground">
          Geen APK-datum ingesteld
        </div>
      </div>
    </div>

    <!-- Timestamps -->
    <div class="rounded-lg border bg-card p-4 shadow-sm">
      <div class="text-sm font-semibold mb-4">
        Systeeminformatie
      </div>
      <div class="grid gap-3 md:grid-cols-2">
        <div>
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Aangemaakt
          </div>
          <div class="text-sm">
            {{ formattedCreatedAt }}
          </div>
        </div>
        <div>
          <div class="text-xs font-medium text-muted-foreground mb-1">
            Bijgewerkt
          </div>
          <div class="text-sm">
            {{ formattedUpdatedAt }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>