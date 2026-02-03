<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  BoxIcon,
  CalendarIcon,
  CircleIcon,
  UserIcon,
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
  // Invert: more days = less progress (closer to inspection = more progress)
  // If days_to_inspection is 365, progress is 0%, if 0, progress is 100%
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
  return `${props.vehicle.days_to_inspection} Days to go`
})

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0]?.toUpperCase() || ''
}

const drivers = computed(() => {
  return props.vehicle.drivers ?? []
})
</script>

<template>
  <div class="w-80 border-r bg-background p-3 space-y-2.5 h-full overflow-y-auto">
    <!-- Header -->
    <div>
      <h2 class="text-sm font-semibold mb-1.5">
        Vehicle
      </h2>
      <div class="text-lg font-bold">
        {{ vehicle.license_plate }}
      </div>
      <div v-if="vehicle.make || vehicle.model" class="text-xs text-muted-foreground mt-0.5">
        {{ vehicle.make }} {{ vehicle.model }}
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Status -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-1.5">
        Status
      </div>
      <Badge
        v-if="vehicle.status"
        :variant="getStatusVariant(vehicle.status)"
        :class="getStatusInfo(vehicle.status)?.color || ''"
      >
        {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
      </Badge>
      <span v-else class="text-sm font-medium">Unknown</span>
    </div>

    <UiSeparator class="my-2" />

    <!-- Vehicle Info -->
    <div class="space-y-2">
      <div v-if="vehicle.make">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Make
        </div>
        <div class="text-sm">
          {{ vehicle.make }}
        </div>
      </div>

      <div v-if="vehicle.model">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Model
        </div>
        <div class="text-sm">
          {{ vehicle.model }}
        </div>
      </div>

      <div v-if="vehicle.year">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Year
        </div>
        <div class="text-sm">
          {{ vehicle.year }}
        </div>
      </div>

      <div v-if="vehicle.color">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Color
        </div>
        <div class="text-sm">
          {{ vehicle.color }}
        </div>
      </div>

      <div v-if="vehicle.vin">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          VIN
        </div>
        <div class="text-sm">
          {{ vehicle.vin }}
        </div>
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Inspection Information -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-1.5">
        Inspection
      </div>
      <div v-if="formattedInspectionDate" class="text-sm mb-1">
        {{ formattedInspectionDate }}
      </div>
      <div v-if="daysToInspectionText" class="space-y-1">
        <div class="text-sm">
          {{ daysToInspectionText }}
        </div>
        <Progress :model-value="inspectionProgressValue" class="h-2" />
      </div>
      <div v-else class="text-sm text-muted-foreground">
        No inspection date set
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Drivers -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-2">
        Drivers
      </div>
      <div v-if="drivers.length === 0" class="text-sm text-muted-foreground">
        No drivers assigned
      </div>
      <div v-else class="space-y-1">
        <div
          v-for="driver in drivers"
          :key="driver.id"
          class="flex items-center gap-2 py-1.5 hover:bg-muted/50 rounded transition-colors"
        >
          <Avatar class="size-6 shrink-0">
            <AvatarFallback class="bg-muted text-xs">
              <UserIcon class="size-3" />
            </AvatarFallback>
          </Avatar>
          <div class="flex-1 min-w-0">
            <div class="text-sm truncate">
              {{ driver.name }}
            </div>
            <div class="text-xs text-muted-foreground truncate">
              {{ driver.email }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Timestamps -->
    <div class="space-y-2">
      <div>
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Created
        </div>
        <div class="text-sm">
          {{ formattedCreatedAt }}
        </div>
      </div>
      <div>
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Updated
        </div>
        <div class="text-sm">
          {{ formattedUpdatedAt }}
        </div>
      </div>
    </div>
  </div>
</template>