<script setup lang="ts">
import { computed } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  BoxIcon,
  CalendarIcon,
  CircleIcon,
  PanelRightCloseIcon,
} from '@/composables/use-icons.composable'
import { statuses } from '@/pages/vehicles/data/data'
import { formatDate } from '@/utils/date'

const props = defineProps<{
  vehicle: IVehicle
}>()

const emit = defineEmits<{
  close: []
}>()

// Get initials from name
function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0]?.toUpperCase() || ''
}

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

function getStatusInfo(status: string | null | undefined) {
  if (!status) {
    return null
  }

  return statuses.find((statusItem) => {
    return statusItem.value.toLowerCase() === status.toLowerCase()
  })
}

function getStatusVariant(status: string | null | undefined) {
  const statusInfo = getStatusInfo(status)
  if (!statusInfo) {
    return 'secondary'
  }

  switch (statusInfo.value) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'destructive'
    case 'maintenance':
      return 'secondary'
    default:
      return 'secondary'
  }
}
</script>

<template>
  <div class="w-80 border-l border-border bg-background flex flex-col h-screen">
    <div class="flex items-center justify-between p-4 border-b shrink-0">
      <h4 class="text-base font-semibold">
        <span class="text-muted-foreground">Vehicle details</span>
        {{ vehicle.license_plate }}
      </h4>
      <button
        class="text-muted-foreground hover:text-foreground transition-colors"
        @click="emit('close')"
      >
        <PanelRightCloseIcon class="size-5" />
        <span class="sr-only">Close sidebar</span>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
      <!-- Time Section -->
      <div class="space-y-5">
        <h4 class="font-semibold text-sm">Time</h4>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <CalendarIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Created</span>
            </div>
            <span class="text-sm font-medium">{{ formattedCreatedAt }}</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <CalendarIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Updated</span>
            </div>
            <span class="text-sm font-medium">{{ formattedUpdatedAt }}</span>
          </div>
          <div
            v-if="vehicle.inspection_date"
            class="flex items-center justify-between"
          >
            <div class="flex items-center gap-2">
              <CalendarIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Inspection Date</span>
            </div>
            <span class="text-sm font-medium">
              {{ formattedInspectionDate }}
            </span>
          </div>
          <div
            v-if="
              vehicle.days_to_inspection !== null &&
              vehicle.days_to_inspection !== undefined
            "
          >
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <CalendarIcon class="size-4 text-muted-foreground" />
                <span class="text-sm text-muted-foreground"
                  >Days to inspection</span
                >
              </div>
              <span class="text-sm font-medium">
                {{ vehicle.days_to_inspection }} Days to go
              </span>
            </div>
            <Progress :model-value="inspectionProgressValue" class="h-2" />
          </div>
        </div>
      </div>

      <!-- Vehicle Info Section -->
      <div class="space-y-3 pt-6 border-t border-border">
        <h4 class="font-semibold text-sm">Vehicle Info</h4>
        <div class="space-y-3">
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <CircleIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Status</span>
            </div>
            <div>
              <Badge
                v-if="vehicle.status"
                :variant="getStatusVariant(vehicle.status)"
                :class="getStatusInfo(vehicle.status)?.color || ''"
              >
                {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
              </Badge>
              <span v-else class="text-sm font-medium">Unknown</span>
            </div>
          </div>
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <BoxIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">License Plate</span>
            </div>
            <span class="text-sm font-medium">
              {{ vehicle.license_plate }}
            </span>
          </div>
          <div
            v-if="vehicle.make"
            class="flex items-center justify-between gap-2"
          >
            <div class="flex items-center gap-2">
              <BoxIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Make</span>
            </div>
            <span class="text-sm font-medium">
              {{ vehicle.make }}
            </span>
          </div>
          <div
            v-if="vehicle.model"
            class="flex items-center justify-between gap-2"
          >
            <div class="flex items-center gap-2">
              <BoxIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Model</span>
            </div>
            <span class="text-sm font-medium">
              {{ vehicle.model }}
            </span>
          </div>
          <div
            v-if="vehicle.year"
            class="flex items-center justify-between gap-2"
          >
            <div class="flex items-center gap-2">
              <CalendarIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Year</span>
            </div>
            <span class="text-sm font-medium">
              {{ vehicle.year }}
            </span>
          </div>
          <div
            v-if="vehicle.color"
            class="flex items-center justify-between gap-2"
          >
            <div class="flex items-center gap-2">
              <CircleIcon class="size-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Color</span>
            </div>
            <span class="text-sm font-medium">
              {{ vehicle.color }}
            </span>
          </div>
        </div>
      </div>

      <!-- Drivers Section -->
      <div
        v-if="vehicle.drivers && vehicle.drivers.length > 0"
        class="space-y-3 pt-6 border-t border-border"
      >
        <h4 class="font-semibold text-sm">Drivers</h4>
        <div class="space-y-2">
          <div
            v-for="driver in vehicle.drivers"
            :key="driver.id"
            class="flex items-center gap-2"
          >
            <Avatar class="size-8">
              <AvatarImage
                v-if="driver.profile_photo_url"
                :src="driver.profile_photo_url"
                :alt="driver.name"
              />
              <AvatarFallback>
                {{ getInitials(driver.name) }}
              </AvatarFallback>
            </Avatar>
            <div class="flex-1">
              <div class="text-sm font-medium">
                {{ driver.name }}
              </div>
              <div class="text-xs text-muted-foreground">
                {{ driver.email }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
