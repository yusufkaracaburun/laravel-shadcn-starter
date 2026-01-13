<script setup lang="ts">
import { computed } from 'vue'

import type {
  IDataTableToolbarProps,
} from '@/components/data-table/types'

import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { XIcon } from '@/composables/use-icons.composable'

import type { IVehicle, IVehicleFilters } from '../models/vehicles'

const props = defineProps<IDataTableToolbarProps<IVehicle, IVehicleFilters>>()

const licensePlateColumn = computed(() => props.table.getColumn('license_plate'))

const searchValue = computed({
  get: () => (licensePlateColumn.value?.getFilterValue() as string) ?? '',
  set: (value: string) => licensePlateColumn.value?.setFilterValue(value),
})

const isFiltered = computed(
  () => props.table.getState().columnFilters.length > 0,
)

function handleResetFilters() {
  props.table.resetColumnFilters()
  props.onClearFilters()
}
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <Input
        v-model="searchValue"
        placeholder="Filter vehicles by license plate..."
        class="h-8 w-[150px] lg:w-[250px] focus-visible:border-input focus-visible:ring-0"
      />

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="handleResetFilters"
      >
        Reset
        <XIcon class="ml-2 size-4" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>
