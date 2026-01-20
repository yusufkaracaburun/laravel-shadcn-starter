<script setup lang="ts">
import { computed } from 'vue'

import type { IEquipment } from '@/pages/equipments/models/equipments'

import { statuses } from '@/pages/equipments/data/data'
import { formatDate } from '@/utils/date'
import { getEquipmentStatusColor } from '@/utils/status-colors'

const props = defineProps<{
  equipment: IEquipment | null
}>()

const createdAt = computed(() =>
  props.equipment?.created_at ? formatDate(props.equipment.created_at) : null,
)

const updatedAt = computed(() =>
  props.equipment?.updated_at ? formatDate(props.equipment.updated_at) : null,
)

function getStatusInfo(status: string | null | undefined) {
  if (!status) return null
  return statuses.find((s) => s.value.toLowerCase() === status.toLowerCase())
}

function getStatusColor(status: string | null | undefined) {
  return getEquipmentStatusColor(status)
}
</script>

<template>
  <div
    class="flex h-full flex-col gap-4 border-l bg-card/80 px-6 py-5 shadow-sm"
  >
    <div
      v-if="!equipment"
      class="my-auto space-y-3 text-center text-muted-foreground"
    >
      <div
        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-muted"
      >
        <UiIcon name="lucide-monitor-cog" class="h-5 w-5" />
      </div>
      <div class="space-y-1">
        <p class="text-sm font-medium text-foreground">No equipment selected</p>
        <p class="text-xs">
          Choose an equipment from the list to view its details.
        </p>
      </div>
    </div>

    <template v-else>
      <header class="space-y-2">
        <div class="flex items-start justify-between gap-3">
          <div class="space-y-1">
            <h2 class="text-lg font-semibold leading-tight">
              {{ equipment.name }}
            </h2>
            <p class="text-xs text-muted-foreground">
              {{ equipment.type || 'Unspecified type' }}
              <span class="mx-1 text-[10px]">•</span>
              {{ equipment.model || 'Unknown model' }}
            </p>
          </div>
        </div>
      </header>

      <UiSeparator class="my-2" />

      <section class="space-y-4 text-sm">
        <div class="space-y-2">
          <h3
            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
          >
            Overview
          </h3>
          <div class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2">
            <div>
              <p class="text-muted-foreground">Type</p>
              <p class="mt-0.5 font-medium">
                {{ equipment.type || 'Unspecified' }}
              </p>
            </div>
            <div>
              <p class="text-muted-foreground">Model</p>
              <p class="mt-0.5 font-medium">
                {{ equipment.model || 'Unknown' }}
              </p>
            </div>
            <div>
              <p class="text-muted-foreground">Serial number</p>
              <p class="mt-0.5 font-medium">
                {{ equipment.serial_number || 'Not set' }}
              </p>
            </div>
          </div>
        </div>

        <UiSeparator />

        <div class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2">
          <div>
            <p class="text-muted-foreground">Status</p>
            <p class="mt-0.5 font-medium">
              <span
                v-if="equipment.status"
                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide"
                :class="[getStatusColor(equipment.status)]"
              >
                {{ getStatusInfo(equipment.status)?.label || equipment.status }}
              </span>
              <span v-else> Unknown </span>
            </p>
          </div>
          <div v-if="createdAt">
            <p class="text-muted-foreground">Created</p>
            <p class="mt-0.5 font-medium">
              {{ createdAt }}
            </p>
          </div>
          <div v-if="updatedAt">
            <p class="text-muted-foreground">Last updated</p>
            <p class="mt-0.5 font-medium">
              {{ updatedAt }}
            </p>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>
