<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { X } from 'lucide-vue-next'

import type { InvoiceFilters } from '@/services/invoices.service'

import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

import type { TInvoice } from '../data/schema'

import InvoicesFilter from './invoices-filter.vue'

interface IDataTableToolbarProps {
  table: Table<TInvoice>
  filters: InvoiceFilters
  onFiltersChange: (filters: InvoiceFilters) => void
  onClearFilters: () => void
}

const props = defineProps<IDataTableToolbarProps>()

const isFiltered = computed(() => {
  return props.table.getState().columnFilters.length > 0 || Object.keys(props.filters).length > 0
})
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <Input
        placeholder="Search invoices..."
        :model-value="(table.getColumn('invoice_number')?.getFilterValue() as string) ?? ''"
        class="h-8 w-[150px] lg:w-[250px]"
        @input="table.getColumn('invoice_number')?.setFilterValue($event.target.value)"
      />

      <InvoicesFilter
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
        <X class="size-4" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>
