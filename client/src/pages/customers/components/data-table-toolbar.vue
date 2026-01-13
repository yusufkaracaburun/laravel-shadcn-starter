<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { XIcon } from '@/composables/use-icons.composable'

import type {
  ICustomer,
  ICustomerFilters,
} from '@/pages/customers/models/customers'

import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

import CustomersFilter from './customers-filter.vue'

interface DataTableToolbarProps {
  table: Table<ICustomer>
  filters: ICustomerFilters
  onFiltersChange: (filters: ICustomerFilters) => void
  onClearFilters: () => void
}

const props = defineProps<DataTableToolbarProps>()

const isFiltered = computed(() => {
  return (
    props.table.getState().columnFilters.length > 0
    || Object.keys(props.filters).length > 0
  )
})
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <Input
        placeholder="Search customers..."
        :model-value="
          (table.getColumn('name')?.getFilterValue() as string) ?? ''
        "
        class="h-8 w-[150px] lg:w-[250px]"
        @input="table.getColumn('name')?.setFilterValue($event.target.value)"
      />

      <CustomersFilter
        :filters="filters"
        :on-filters-change="onFiltersChange"
        :on-clear="onClearFilters"
      />

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="
          () => {
            table.resetColumnFilters()
            onClearFilters()
          }
        "
      >
        Reset
        <XIcon class="size-4" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>
