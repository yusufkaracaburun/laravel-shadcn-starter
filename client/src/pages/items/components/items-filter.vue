<script setup lang="ts">
import { Check, CirclePlus, Search, X } from 'lucide-vue-next'

import type { ItemFilters } from '@/services/items.service'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Separator } from '@/components/ui/separator'

import { units, vatRates } from '../data/data'

interface Props {
  filters: ItemFilters
  onFiltersChange: (filters: ItemFilters) => void
  onClear: () => void
}

const props = defineProps<Props>()

const isOpen = ref(false)
const searchFilter = ref('')

// Local state for filters
const localFilters = ref<ItemFilters>({ ...props.filters })

// Watch for external filter changes
watch(
  () => props.filters,
  (newFilters) => {
    localFilters.value = { ...newFilters }
    searchFilter.value = newFilters.search || ''
  },
  { deep: true },
)

// Initialize search filter
watchEffect(() => {
  if (props.filters.search) {
    searchFilter.value = props.filters.search
  }
})

// Count active filters
const activeFilterCount = computed(() => {
  let count = 0
  if (localFilters.value.unit) count++
  if (localFilters.value.vat_rate !== undefined) count++
  if (localFilters.value.search) count++
  if (localFilters.value.created_at) count++
  if (localFilters.value.updated_at) count++
  return count
})

// Selected unit
const selectedUnit = computed(() => {
  return localFilters.value.unit
    ? units.find((u) => u.value === localFilters.value.unit)
    : null
})

// Selected VAT rate
const selectedVatRate = computed(() => {
  return localFilters.value.vat_rate !== undefined
    ? vatRates.find((v) => Number(v.value) === localFilters.value.vat_rate)
    : null
})

function handleUnitSelect(unit: string) {
  if (localFilters.value.unit === unit) {
    delete localFilters.value.unit
  } else {
    localFilters.value.unit = unit
  }
  applyFilters()
}

function handleVatRateSelect(vatRate: number) {
  if (localFilters.value.vat_rate === vatRate) {
    delete localFilters.value.vat_rate
  } else {
    localFilters.value.vat_rate = vatRate
  }
  applyFilters()
}

function handleSearchChange(value: string) {
  searchFilter.value = value
  if (value.trim()) {
    localFilters.value.search = value.trim()
  } else {
    delete localFilters.value.search
  }
  applyFilters()
}

function handleDateFilterChange(
  type: 'created_at' | 'updated_at',
  value: string,
) {
  if (value) {
    localFilters.value[type] = value
  } else {
    delete localFilters.value[type]
  }
  applyFilters()
}

function applyFilters() {
  props.onFiltersChange({ ...localFilters.value })
}

function handleClear() {
  localFilters.value = {}
  searchFilter.value = ''
  props.onClear()
  isOpen.value = false
}

function handleOpenChange(open: boolean) {
  isOpen.value = open
  if (!open) {
    // Reset local filters to current props when closing without applying
    localFilters.value = { ...props.filters }
    searchFilter.value = props.filters.search || ''
  }
}
</script>

<template>
  <Popover v-model:open="isOpen" @update:open="handleOpenChange">
    <PopoverTrigger as-child>
      <Button variant="outline" size="sm" class="h-8 border-dashed">
        <CirclePlus class="size-4 mr-2" />
        Filters
        <template v-if="activeFilterCount > 0">
          <Separator orientation="vertical" class="h-4 mx-2" />
          <span class="text-xs font-medium">{{ activeFilterCount }}</span>
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[300px] p-0" align="start">
      <div class="p-4 space-y-4">
        <!-- Search Filters Input -->
        <div class="space-y-2">
          <Label class="text-sm font-medium">Search filters</Label>
          <div class="relative">
            <Search
              class="absolute left-2 top-2.5 size-4 text-muted-foreground"
            />
            <Input
              v-model="searchFilter"
              placeholder="Search filters..."
              class="pl-8"
              @input="handleSearchChange(searchFilter)"
            />
          </div>
        </div>

        <Separator />

        <!-- Unit Filter -->
        <div class="space-y-2">
          <Label class="text-sm font-medium">Unit</Label>
          <div class="space-y-1 max-h-[200px] overflow-y-auto">
            <button
              v-for="unit in units"
              :key="unit.value"
              class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-accent"
              :class="{ 'bg-accent': selectedUnit?.value === unit.value }"
              @click="handleUnitSelect(unit.value)"
            >
              <span>{{ unit.label }}</span>
              <Check
                v-if="selectedUnit?.value === unit.value"
                class="size-4 text-primary"
              />
            </button>
          </div>
        </div>

        <Separator />

        <!-- VAT Rate Filter -->
        <div class="space-y-2">
          <Label class="text-sm font-medium">VAT Rate</Label>
          <div class="space-y-1 max-h-[200px] overflow-y-auto">
            <button
              v-for="vatRate in vatRates"
              :key="vatRate.value"
              class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-accent"
              :class="{ 'bg-accent': selectedVatRate?.value === vatRate.value }"
              @click="handleVatRateSelect(Number(vatRate.value))"
            >
              <span>{{ vatRate.label }}</span>
              <Check
                v-if="selectedVatRate?.value === vatRate.value"
                class="size-4 text-primary"
              />
            </button>
          </div>
        </div>

        <Separator />

        <!-- Date Filters -->
        <div class="space-y-3">
          <Label class="text-sm font-medium">Created At</Label>
          <Input
            type="date"
            :value="localFilters.created_at || ''"
            @input="
              handleDateFilterChange(
                'created_at',
                ($event.target as HTMLInputElement).value,
              )
            "
          />
        </div>

        <div class="space-y-3">
          <Label class="text-sm font-medium">Updated At</Label>
          <Input
            type="date"
            :value="localFilters.updated_at || ''"
            @input="
              handleDateFilterChange(
                'updated_at',
                ($event.target as HTMLInputElement).value,
              )
            "
          />
        </div>

        <Separator />

        <!-- Clear Filters -->
        <div v-if="activeFilterCount > 0" class="pt-2">
          <Button
            variant="ghost"
            class="w-full justify-center"
            @click="handleClear"
          >
            <X class="size-4 mr-2" />
            Clear filters
          </Button>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
