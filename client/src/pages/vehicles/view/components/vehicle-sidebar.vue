<script setup lang="ts">
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import DetailsSidebar from '@/components/global-layout/components/details-page/details-sidebar.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Progress } from '@/components/ui/progress'
import { UserIcon } from '@/composables/use-icons.composable'
import { useVehicleStatus } from '@/pages/vehicles/composables/use-vehicle-status.composable'
import { formatDate } from '@/utils/date'

interface Props {
  vehicle: IVehicle
}

const props = defineProps<Props>()

const emits = defineEmits<{
  navigateToTab: [tab: 'drivers']
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

const drivers = computed(() => {
  return props.vehicle.drivers ?? []
})

const statusInfo = computed(() => {
  if (!props.vehicle.status) {
    return undefined
  }
  const info = getStatusInfo(props.vehicle.status)
  return {
    label: info?.label || props.vehicle.status,
    variant: getStatusVariant(props.vehicle.status) as 'default' | 'destructive' | 'secondary' | 'outline',
    color: info?.color,
  }
})

const fields = computed(() => {
  const result = []
  if (props.vehicle.make) {
    result.push({ label: 'Merk', value: props.vehicle.make })
  }
  if (props.vehicle.model) {
    result.push({ label: 'Model', value: props.vehicle.model })
  }
  if (props.vehicle.year) {
    result.push({ label: 'Jaar', value: props.vehicle.year })
  }
  if (props.vehicle.color) {
    result.push({ label: 'Kleur', value: props.vehicle.color })
  }
  if (props.vehicle.vin) {
    result.push({ label: 'VIN', value: props.vehicle.vin })
  }
  return result
})

const relatedItems = computed(() => {
  return drivers.value.map(driver => ({
    id: driver.id,
    name: driver.name,
    icon: UserIcon,
  }))
})

function handleNavigateToTab() {
  emits('navigateToTab', 'drivers')
}
</script>

<template>
  <DetailsSidebar
    title="Voertuig"
    :subtitle="vehicle.make || vehicle.model ? `${vehicle.make} ${vehicle.model}` : undefined"
    :status="statusInfo"
    :fields="fields"
    :related-items="relatedItems"
    related-items-label="Bestuurders"
    related-items-empty-text="Geen bestuurders toegewezen"
    :show-view-all="drivers.length > 3"
    view-all-text="meer"
    :timestamps="{
      created: formattedCreatedAt,
      updated: formattedUpdatedAt,
    }"
    :on-navigate-to-tab="handleNavigateToTab"
  >
    <template #header-title>
      {{ vehicle.license_plate }}
    </template>

    <template #status>
      <!-- TODO: Implement inline status change -->
      <Badge
        v-if="vehicle.status"
        :variant="getStatusVariant(vehicle.status)"
        :class="getStatusInfo(vehicle.status)?.color || ''"
      >
        {{ getStatusInfo(vehicle.status)?.label || vehicle.status }}
      </Badge>
      <span v-else class="text-sm font-medium">Onbekend</span>
    </template>

    <template #custom-section>
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
    </template>
  </DetailsSidebar>
</template>
