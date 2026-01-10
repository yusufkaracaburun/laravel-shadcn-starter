<script setup lang="ts">
import { Filter } from 'lucide-vue-next'

import type { ICustomerFilters } from '@/pages/customers/models/customers'

import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface CustomersFilterProps {
  filters: ICustomerFilters
  onFiltersChange: (filters: ICustomerFilters) => void
  onClear: () => void
}

const props = defineProps<CustomersFilterProps>()

const localFilters = ref<ICustomerFilters>({ ...props.filters })

watch(
  () => props.filters,
  (newFilters) => {
    localFilters.value = { ...newFilters }
  },
  { deep: true },
)

function updateFilter(key: keyof ICustomerFilters, value: any) {
  localFilters.value = {
    ...localFilters.value,
    [key]: value === 'all' || !value ? undefined : value,
  }
  props.onFiltersChange(localFilters.value)
}

function clearFilters() {
  localFilters.value = {}
  props.onClear()
}

const hasActiveFilters = computed(() => {
  return Object.keys(localFilters.value).length > 0
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="h-8">
        <Filter class="mr-2 size-4" />
        Filters
        <span
          v-if="hasActiveFilters"
          class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground"
        >
          {{ Object.keys(localFilters).length }}
        </span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[200px] p-0" align="start">
      <div class="p-4 space-y-4">
        <div class="space-y-2">
          <label class="text-sm font-medium">Type</label>
          <Select
            :model-value="localFilters.type || 'all'"
            @update:model-value="(value) => updateFilter('type', value)"
          >
            <SelectTrigger>
              <SelectValue placeholder="All types" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                All types
              </SelectItem>
              <SelectItem value="business">
                Business
              </SelectItem>
              <SelectItem value="private">
                Private
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium">City</label>
          <input
            v-model="localFilters.city"
            type="text"
            placeholder="Filter by city"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="
              updateFilter('city', ($event.target as HTMLInputElement).value)
            "
          >
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium">Country</label>
          <input
            v-model="localFilters.country"
            type="text"
            placeholder="Filter by country"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="
              updateFilter('country', ($event.target as HTMLInputElement).value)
            "
          >
        </div>

        <Button
          v-if="hasActiveFilters"
          variant="ghost"
          class="w-full"
          @click="clearFilters"
        >
          Clear Filters
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
