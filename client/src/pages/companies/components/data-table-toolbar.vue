<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { X } from 'lucide-vue-next'

import DataTableFacetedFilter from '@/components/data-table/faceted-filter.vue'
import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

import type { Company } from '../data/schema'

import { employeeSizes, industries, statuses } from '../data/data'

interface DataTableToolbarProps {
  table: Table<Company>
}

const props = defineProps<DataTableToolbarProps>()

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0)
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <Input
        placeholder="Filter companies..."
        :model-value="(table.getColumn('name')?.getFilterValue() as string) ?? ''"
        class="h-8 w-[150px] lg:w-[250px]"
        @input="table.getColumn('name')?.setFilterValue($event.target.value)"
      />

      <div class="space-x-2">
        <DataTableFacetedFilter
          v-if="table.getColumn('status')"
          :column="table.getColumn('status')"
          title="Status"
          :options="statuses"
        />
        <DataTableFacetedFilter
          v-if="table.getColumn('employees')"
          :column="table.getColumn('employees')"
          title="Employees"
          :options="employeeSizes"
        />
        <DataTableFacetedFilter
          v-if="table.getColumn('industry')"
          :column="table.getColumn('industry')"
          title="Industry"
          :options="industries"
        />
      </div>

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="table.resetColumnFilters()"
      >
        Reset
        <X class="size-4" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>

