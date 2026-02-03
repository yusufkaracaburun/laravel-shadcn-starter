<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import {
  BoxIcon,
  CalendarIcon,
  CircleIcon,
  FileTextIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { formatDate } from '@/utils/date'

import { useVehicleStatus } from '@/pages/vehicles/composables/use-vehicle-status.composable'
import VehicleDocumentsSection from '@/pages/vehicles/components/vehicle-documents-section.vue'
import VehicleDriversSection from '@/pages/vehicles/components/vehicle-drivers-section.vue'

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
  return `${props.vehicle.days_to_inspection} Days to go`
})
</script>

<template>
  <div class="space-y-2">
    <!-- Basic Information -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Basisinformatie
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              License Plate
            </div>
            <div class="text-sm">
              {{ vehicle.license_plate }}
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Status
            </div>
            <div class="text-sm">
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
          <div v-if="vehicle.vin" class="md:col-span-2">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              VIN
            </div>
            <div class="text-sm">
              {{ vehicle.vin }}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Inspection Information -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Inspection Information
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div v-if="formattedInspectionDate">
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              Inspection Date
            </div>
            <div class="text-sm">
              {{ formattedInspectionDate }}
            </div>
          </div>
          <div v-if="daysToInspectionText">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Days to Inspection
            </div>
            <div class="text-sm mb-1">
              {{ daysToInspectionText }}
            </div>
            <Progress :model-value="inspectionProgressValue" class="h-2" />
          </div>
          <div
            v-if="!formattedInspectionDate && !daysToInspectionText"
            class="md:col-span-2 text-sm text-muted-foreground"
          >
            No inspection date set
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Drivers & Documents -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Drivers & Documents
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
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
      </CardContent>
    </Card>

    <!-- System Information -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Systeeminformatie
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div>
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              Aangemaakt op
            </div>
            <div class="text-sm">
              {{ formattedCreatedAt }}
            </div>
          </div>
          <div>
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              Bijgewerkt op
            </div>
            <div class="text-sm">
              {{ formattedUpdatedAt }}
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Vehicle ID
            </div>
            <div class="text-sm">
              #{{ vehicle.id }}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>