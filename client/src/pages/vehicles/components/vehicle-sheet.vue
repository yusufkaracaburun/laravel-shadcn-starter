<script setup lang="ts">
import { computed } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'
import {
  BoxIcon,
  CalendarIcon,
  CircleIcon,
  FileTextIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { statuses } from '@/pages/vehicles/data/data'
import { formatDate } from '@/utils/date'

import VehicleDocumentsSection from './vehicle-documents-section.vue'
import VehicleDriversSection from './vehicle-drivers-section.vue'

const props = defineProps<{
  vehicle: IVehicle
  open: boolean
}>()

const emit = defineEmits<{
  'close': []
  'update:open': [value: boolean]
}>()

const sheetOpen = computed({
  get: () => props.open,
  set: (value) => {
    emit('update:open', value)
    if (!value) {
      emit('close')
    }
  },
})

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
  <Sheet v-model:open="sheetOpen">
    <SheetContent
      side="right"
      class="w-full shadow-xl"
    >
      <div class="flex flex-col h-full">
        <SheetHeader>
          <SheetTitle>
            {{ vehicle.license_plate }}
          </SheetTitle>
          <SheetDescription>
            Vehicle details
          </SheetDescription>
        </SheetHeader>

        <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-6">
          <!-- Time Section -->
          <div class="space-y-5">
            <h4 class="font-semibold text-sm">
              Time
            </h4>
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
                    <span class="text-sm text-muted-foreground">
                      Days to inspection
                    </span>
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
          <div class="space-y-4 pt-6 border-t border-border">
            <h4 class="font-semibold text-sm">
              Vehicle Info
            </h4>
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

          <!-- Drivers & Documents Section -->
          <div
            v-if="vehicle.drivers && vehicle.drivers.length > 0"
            class="space-y-4 pt-6 border-t border-border"
          >
            <h4 class="font-semibold text-sm">
              Drivers & Documents
            </h4>
            <div class="space-y-3">
              <UiTabs default-value="drivers" class="w-full space-y-3">
                <UiTabsList
                  class="inline-flex items-center gap-2 rounded-full bg-muted px-1.5 py-1"
                >
                  <UiTabsTrigger
                    value="drivers"
                    class="flex-none rounded-full border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-muted-foreground data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:border-border data-[state=active]:shadow-sm"
                  >
                    <UsersIcon class="mr-1 h-3.5 w-3.5" />
                    Drivers
                  </UiTabsTrigger>
                  <UiTabsTrigger
                    value="documents"
                    class="flex-none rounded-full border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-muted-foreground data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:border-border data-[state=active]:shadow-sm"
                  >
                    <FileTextIcon class="mr-1 h-3.5 w-3.5" />
                    Documents
                  </UiTabsTrigger>
                </UiTabsList>

                <UiTabsContent value="drivers" class="space-y-3">
                  <VehicleDriversSection :vehicle="vehicle" />
                </UiTabsContent>

                <UiTabsContent value="documents" class="space-y-3">
                  <VehicleDocumentsSection />
                </UiTabsContent>
              </UiTabs>
            </div>
          </div>
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>
