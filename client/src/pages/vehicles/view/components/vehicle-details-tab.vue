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
              Kenteken
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
          APK-informatie
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div v-if="formattedInspectionDate">
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              APK-datum
            </div>
            <div class="text-sm">
              {{ formattedInspectionDate }}
            </div>
          </div>
          <div v-if="daysToInspectionText">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Dagen tot APK
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
            Geen APK-datum ingesteld
          </div>
        </div>
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
              Voertuig ID
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
