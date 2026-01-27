<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import type {
  IEquipment,
  IEquipmentFilters,
} from '@/pages/equipments/models/equipments'
import type { ISorting } from '@/services/query-utils'

import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from '@/components/ui/input-group'
import {
  ChevronsUpDownIcon,
  FilterIcon,
  SearchIcon,
} from '@/composables/use-icons.composable'
import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'
import { statuses } from '@/pages/equipments/data/data'

const props = withDefaults(
  defineProps<{
    equipments: IEquipment[]
    loading?: boolean
    selectedEquipment?: IEquipment | null
    openDetail?: () => void
    filter?: IEquipmentFilters
    sort?: ISorting
  }>(),
  {
    loading: false,
    selectedEquipment: null,
    openDetail: () => {},
    filter: undefined,
    sort: undefined,
  },
)

const emit = defineEmits<{
  'update:selectedEquipment': [equipment: IEquipment | null]
  'select': [equipment: IEquipment]
  'filtersChange': [filters: IEquipmentFilters]
  'sortChange': [sort: ISorting]
}>()

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

const { equipmentsPrerequisitesResponse } = useEquipments()

const prerequisitesStatuses = computed(() => {
  const response = equipmentsPrerequisitesResponse.value
  if (!response) {
    return []
  }
  // Handle both direct data and wrapped response
  const data = (response as any)?.data ?? response
  return (data as any)?.statuses ?? []
})

const prerequisitesTypes = computed(() => {
  const response = equipmentsPrerequisitesResponse.value
  if (!response) {
    return []
  }
  // Handle both direct data and wrapped response
  const data = (response as any)?.data ?? response
  return (data as any)?.types ?? []
})

// Map backend statuses to our status config for icons
function getStatusConfig(statusValue: string) {
  return statuses.find(s => s.value.toLowerCase() === statusValue.toLowerCase())
}

const searchTerm = ref(props.filter?.search || '')
const selectedStatus = ref<string | null>(props.filter?.status || null)
const selectedType = ref<string | null>(props.filter?.type || null)

function getSortString(sort: ISorting | undefined): string {
  if (!sort) {
    return 'created_at-desc'
  }
  const direction = sort.desc ? 'desc' : 'asc'
  return `${sort.id}-${direction}`
}

const selectedSort = ref<string>(getSortString(props.sort))

// Watch for filter changes from parent
watch(
  () => props.filter,
  (newFilter) => {
    searchTerm.value = newFilter?.search || ''
    selectedStatus.value = newFilter?.status || null
    selectedType.value = newFilter?.type || null
  },
  { deep: true },
)

// Watch for sort changes from parent
watch(
  () => props.sort,
  (newSort) => {
    selectedSort.value = getSortString(newSort)
  },
  { deep: true },
)

// Debounce search updates
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchTerm, () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    updateFilters()
  }, 300)
})

function updateFilters() {
  const filters: IEquipmentFilters = {}

  if (searchTerm.value.trim()) {
    filters.search = searchTerm.value.trim()
  }

  if (selectedStatus.value) {
    filters.status = selectedStatus.value
  }

  if (selectedType.value) {
    filters.type = selectedType.value
  }

  emit('filtersChange', filters)
}

function handleStatusFilter(statusValue: string) {
  if (selectedStatus.value === statusValue.toLowerCase()) {
    selectedStatus.value = null
  } else {
    selectedStatus.value = statusValue.toLowerCase()
  }
  updateFilters()
}

function handleTypeFilter(type: string) {
  if (selectedType.value === type) {
    selectedType.value = null
  } else {
    selectedType.value = type
  }
  updateFilters()
}

function handleSortChange(sortValue: string) {
  selectedSort.value = sortValue
  const [id, direction] = sortValue.split('-')
  emit('sortChange', {
    id,
    desc: direction === 'desc',
  })
}

const filteredEquipments = computed(() => {
  return props.equipments ?? []
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
      currentId != null && next.some(equipment => equipment.id === currentId)

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
</script>

<template>
  <div
    class="flex flex-col gap-4 h-full min-h-0 px-2"
    data-testid="equipment_page-layout"
  >
    <div class="space-y-3">
      <UiLabel
        class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
      >
        Search
      </UiLabel>
      <div class="flex items-center gap-2">
        <InputGroup class="flex-1">
          <InputGroupInput
            v-model="searchTerm"
            placeholder="Search equipment by name, model, serial number..."
          />
          <InputGroupAddon>
            <SearchIcon />
          </InputGroupAddon>
        </InputGroup>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="icon" class="h-9 w-9 shrink-0">
              <FilterIcon class="size-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56 max-h-[300px] overflow-y-auto">
            <DropdownMenuLabel class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
              Filter by Status
            </DropdownMenuLabel>
            <DropdownMenuItem
              v-for="status in prerequisitesStatuses"
              :key="status.value"
              :class="selectedStatus === status.value.toLowerCase() ? 'bg-muted' : ''"
              @select="handleStatusFilter(status.value)"
            >
              <div class="flex items-center gap-2">
                <component
                  :is="getStatusConfig(status.value)?.icon"
                  class="size-4"
                />
                {{ status.label }}
              </div>
            </DropdownMenuItem>

            <DropdownMenuSeparator v-if="prerequisitesTypes.length > 0" />

            <DropdownMenuLabel v-if="prerequisitesTypes.length > 0" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
              Filter by Type
            </DropdownMenuLabel>
            <DropdownMenuItem
              v-for="type in prerequisitesTypes"
              :key="type"
              :class="selectedType === type ? 'bg-muted' : ''"
              @select="handleTypeFilter(type)"
            >
              {{ type }}
            </DropdownMenuItem>

            <DropdownMenuSeparator v-if="selectedStatus || selectedType" />
            <DropdownMenuItem
              v-if="selectedStatus || selectedType"
              @select="() => { selectedStatus = null; selectedType = null; updateFilters() }"
            >
              Clear filters
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="icon" class="h-9 w-9 shrink-0">
              <ChevronsUpDownIcon class="size-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-48">
            <DropdownMenuLabel>Sort by</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              :class="selectedSort === 'name-asc' ? 'bg-muted' : ''"
              @select="handleSortChange('name-asc')"
            >
              Name: A to Z
            </DropdownMenuItem>
            <DropdownMenuItem
              :class="selectedSort === 'name-desc' ? 'bg-muted' : ''"
              @select="handleSortChange('name-desc')"
            >
              Name: Z to A
            </DropdownMenuItem>
            <DropdownMenuItem
              :class="selectedSort === 'created_at-desc' ? 'bg-muted' : ''"
              @select="handleSortChange('created_at-desc')"
            >
              Newest first
            </DropdownMenuItem>
            <DropdownMenuItem
              :class="selectedSort === 'created_at-asc' ? 'bg-muted' : ''"
              @select="handleSortChange('created_at-asc')"
            >
              Oldest first
            </DropdownMenuItem>
            <DropdownMenuItem
              :class="selectedSort === 'status-asc' ? 'bg-muted' : ''"
              @select="handleSortChange('status-asc')"
            >
              Status
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>

    <div
      v-if="!loading && !filteredEquipments.length"
      class="flex flex-1 items-center justify-center rounded-xl border border-dashed bg-muted/40 px-6 py-10 text-center text-sm text-muted-foreground"
    >
      <div class="space-y-2">
        <div class="text-base font-medium">
          No equipments found
        </div>
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
                  selectedEquipment && selectedEquipment.id === equipment.id,
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
                  <Badge
                    v-if="equipment.status"
                    :variant="getStatusVariant(equipment.status)"
                    :class="getStatusInfo(equipment.status)?.color || ''"
                  >
                    {{
                      getStatusInfo(equipment.status)?.label || equipment.status
                    }}
                  </Badge>
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
