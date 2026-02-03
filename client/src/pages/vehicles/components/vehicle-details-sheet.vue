<script setup lang="ts">
import { computed } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import InfoItem from '@/components/global-layout/components/info-item.vue'
import InfoSection
  from '@/components/global-layout/components/info-section.vue'
import SheetDrawer
  from '@/components/global-layout/components/sheet-drawer.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import {
  BoxIcon,
  CalendarIcon,
  CircleIcon,
  FileTextIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { formatDate } from '@/utils/date'

import { useVehicleStatus } from '../composables/use-vehicle-status.composable'
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
</script>

<template>
  <SheetDrawer
    :title="vehicle.license_plate"
    description="Vehicle details"
    :open="open"
    side="right"
    @close="emit('close')"
    @update:open="(value) => emit('update:open', value)"
  >
    <!-- Time Section -->
    <InfoSection title="Time" title-spacing="lg">
      <InfoItem
        label="Created"
        :value="formattedCreatedAt"
        :icon="CalendarIcon"
      />
      <InfoItem
        label="Updated"
        :value="formattedUpdatedAt"
        :icon="CalendarIcon"
      />
      <InfoItem
        label="Inspection Date"
        :value="formattedInspectionDate"
        :icon="CalendarIcon"
      />
      <div v-if="daysToInspectionText">
        <InfoItem
          label="Days to inspection"
          :value="daysToInspectionText"
          :icon="CalendarIcon"
        />
        <Progress :model-value="inspectionProgressValue" class="h-2 mt-1" />
      </div>
    </InfoSection>

    <!-- Vehicle Info Section -->
    <InfoSection title="Vehicle Info" with-border>
      <InfoItem
        label="Status"
        :value="vehicle.status"
        :icon="CircleIcon"
      >
        <template #value>
          <Badge
            v-if="vehicle.status"
            :variant="getStatusVariant(vehicle.status)"
            :class="getStatusInfo(vehicle.status)?.color || ''"
          >
            {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
          </Badge>
          <span v-else class="text-sm font-medium">Unknown</span>
        </template>
      </InfoItem>
      <InfoItem
        label="License Plate"
        :value="vehicle.license_plate"
        :icon="BoxIcon"
      />
      <InfoItem
        label="Make"
        :value="vehicle.make"
        :icon="BoxIcon"
      />
      <InfoItem
        label="Model"
        :value="vehicle.model"
        :icon="BoxIcon"
      />
      <InfoItem
        label="Year"
        :value="vehicle.year"
        :icon="CalendarIcon"
      />
      <InfoItem
        label="Color"
        :value="vehicle.color"
        :icon="CircleIcon"
      />
    </InfoSection>

    <!-- Drivers & Documents Section -->
    <InfoSection
      v-if="vehicle.drivers && vehicle.drivers.length > 0"
      title="Drivers & Documents"
      with-border
    >
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
    </InfoSection>
  </SheetDrawer>
</template>
