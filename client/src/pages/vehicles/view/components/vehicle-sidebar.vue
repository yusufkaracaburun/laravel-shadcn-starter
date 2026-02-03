<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  UserIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useVehicleStatus } from '@/pages/vehicles/composables/use-vehicle-status.composable'
import { formatDate } from '@/utils/date'

interface Props {
  vehicle: IVehicle
}

const props = defineProps<Props>()

const emits = defineEmits<{
  (e: 'navigate-to-tab', tab: 'drivers'): void
}>()

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
  return `${props.vehicle.days_to_inspection} dagen te gaan`
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

const maxDisplayedDrivers = 3

const displayedDrivers = computed(() => {
  return drivers.value.slice(0, maxDisplayedDrivers)
})

const remainingDriversCount = computed(() => {
  return Math.max(0, drivers.value.length - maxDisplayedDrivers)
})

function handleViewAllDrivers() {
  emits('navigate-to-tab', 'drivers')
}
</script>

<template>
  <div class="w-80 border-r bg-background p-3 space-y-2.5 h-full overflow-y-auto">
    <!-- Header -->
    <div>
      <h2 class="text-sm font-semibold mb-1.5">
        Voertuig
      </h2>
      <div class="text-lg font-bold">
        {{ vehicle.license_plate }}
      </div>
      <div
        v-if="vehicle.make || vehicle.model"
        class="text-xs text-muted-foreground mt-0.5"
      >
        {{ vehicle.make }} {{ vehicle.model }}
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Status -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-0.5">
        Status
      </div>
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
      <span v-else class="text-sm font-medium">Unknown</span>
    </div>

    <UiSeparator class="my-2" />

    <!-- Vehicle Info -->
    <div class="space-y-2">
      <div v-if="vehicle.make">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Merk
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
          Jaar
        </div>
        <div class="text-sm">
          {{ vehicle.year }}
        </div>
      </div>

      <div v-if="vehicle.color">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Kleur
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
      <div class="text-xs font-medium text-muted-foreground mb-1">
        APK
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
        Geen APK-datum ingesteld
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Drivers -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-2">
        Bestuurders
      </div>
      <div v-if="drivers.length === 0" class="text-sm text-muted-foreground py-2">
        Geen bestuurders toegewezen
      </div>
      <div v-else class="space-y-1">
        <div
          v-for="driver in displayedDrivers"
          :key="driver.id"
          class="group flex items-center justify-between gap-2 py-1.5 hover:bg-muted/50 rounded transition-colors"
        >
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <Avatar class="size-6 shrink-0">
              <AvatarFallback class="bg-muted text-xs">
                <UserIcon class="size-3" />
              </AvatarFallback>
            </Avatar>
            <div class="text-sm truncate">
              {{ driver.name }}
            </div>
          </div>
        </div>
        <button
          v-if="remainingDriversCount > 0"
          type="button"
          class="w-full text-left text-xs font-medium text-primary hover:text-primary/80 transition-colors py-2 px-2 rounded-md hover:bg-primary/5 flex items-center gap-1.5 group"
          @click="handleViewAllDrivers"
        >
          <span>+{{ remainingDriversCount }} meer</span>
          <UsersIcon class="size-3 opacity-60 group-hover:opacity-100 transition-opacity" />
        </button>
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Timestamps -->
    <div class="space-y-2">
      <div>
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Aangemaakt
        </div>
        <div class="text-sm">
          {{ formattedCreatedAt }}
        </div>
      </div>
      <div>
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Bijgewerkt
        </div>
        <div class="text-sm">
          {{ formattedUpdatedAt }}
        </div>
      </div>
    </div>
  </div>
</template>
