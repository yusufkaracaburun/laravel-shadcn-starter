<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import type { IEquipment } from '@/pages/equipments/models/equipments'
import { statuses } from '@/pages/equipments/data/data'
import { getEquipmentStatusColor } from '@/utils/status-colors'

const props = withDefaults(
  defineProps<{
    equipments: IEquipment[]
    loading?: boolean
    selectedEquipment?: IEquipment | null
    openDetail?: () => void
  }>(),
  {
    loading: false,
    selectedEquipment: null,
    openDetail: () => {},
  },
)

const emit = defineEmits<{
  'update:selectedEquipment': [equipment: IEquipment | null]
  'select': [equipment: IEquipment]
}>()

const searchTerm = ref('')

const filteredEquipments = computed(() => {
  const items = props.equipments ?? []
  const query = searchTerm.value.trim().toLowerCase()

  if (!query) {
    return items
  }

  return items.filter((equipment: IEquipment) => {
    const haystack = [
      equipment.name,
      equipment.type,
      equipment.model,
      equipment.serial_number,
      equipment.status,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(query)
  })
})

watch(
  filteredEquipments,
  (next) => {
    if (!next.length) {
      emit('update:selectedEquipment', null)
      return
    }

    const currentId = props.selectedEquipment?.id
    const stillExists =
      currentId != null && next.some((equipment) => equipment.id === currentId)

    if (!stillExists) {
      emit('update:selectedEquipment', next[0])
    }
  },
  { immediate: true },
)

function handleSelectEquipment(equipment: IEquipment) {
  emit('update:selectedEquipment', equipment)
  emit('select', equipment)
  props.openDetail()
}

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
    class="flex flex-col gap-4 h-full min-h-0"
    data-testid="equipment_page-layout"
  >
    <div class="space-y-3">
      <UiLabel
        class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
      >
        Search
      </UiLabel>
      <UiInput
        v-model="searchTerm"
        placeholder="Search equipment by name, model, serial number..."
        class="w-full"
      />
    </div>

    <div
      v-if="!loading && !filteredEquipments.length"
      class="flex flex-1 items-center justify-center rounded-xl border border-dashed bg-muted/40 px-6 py-10 text-center text-sm text-muted-foreground"
    >
      <div class="space-y-2">
        <div class="text-base font-medium">No equipments found</div>
        <p class="max-w-xs mx-auto">
          Try adjusting your search or create a new equipment to get started.
        </p>
      </div>
    </div>

    <div
      v-else
      class="relative flex-1 min-h-0 rounded-xl border bg-background/80 shadow-sm"
    >
      <div
        v-if="loading"
        class="absolute inset-0 z-10 flex items-center justify-center bg-background/60 backdrop-blur-sm"
      >
        <UiSpinner class="h-5 w-5 text-muted-foreground" />
      </div>

      <UiScrollArea class="h-full">
        <ul class="divide-y">
          <li v-for="equipment in filteredEquipments" :key="equipment.id">
            <button
              type="button"
              class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-muted/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              :class="{
                'bg-muted/70 border-l-2 border-l-primary':
                  selectedEquipment &&
                  selectedEquipment.id === equipment.id,
              }"
              @click="handleSelectEquipment(equipment)"
            >
              <div
                class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
              >
                {{ equipment.name?.charAt(0)?.toUpperCase() || 'E' }}
              </div>
              <div class="min-w-0 flex-1 space-y-0.5">
                <div class="flex items-center gap-2">
                  <span class="truncate text-sm font-medium">
                    {{ equipment.name }}
                  </span>
                  <UiBadge
                    v-if="equipment.status"
                    :class="[
                      getStatusColor(equipment.status),
                      'px-1 py-0 text-[10px] font-medium uppercase tracking-wide',
                    ]"
                  >
                    {{ getStatusInfo(equipment.status)?.label || equipment.status }}
                  </UiBadge>
                </div>
                <p class="truncate text-xs text-muted-foreground">
                  {{ equipment.type || 'Unspecified type' }}
                  <span class="mx-1 text-[10px]">•</span>
                  {{ equipment.model || 'Unknown model' }}
                </p>
                <p
                  v-if="equipment.serial_number"
                  class="truncate text-xs text-muted-foreground"
                >
                  Serial: {{ equipment.serial_number }}
                </p>
              </div>
            </button>
          </li>
        </ul>
      </UiScrollArea>
    </div>
  </div>
</template>
