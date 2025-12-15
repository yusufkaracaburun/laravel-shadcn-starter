<script setup lang="ts">
import type { DataTableProps } from '@/components/data-table/types'
import type { CustomerFilters } from '@/services/customers.service'

import { Trash2Icon } from 'lucide-vue-next'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import type { Customer } from '../data/schema'

import CustomerDeleteBatch from './customer-delete-batch.vue'
import DataTableToolbar from './data-table-toolbar.vue'

interface ExtendedDataTableProps extends DataTableProps<Customer> {
  filters?: CustomerFilters
  onFiltersChange?: (filters: CustomerFilters) => void
  onClearFilters?: () => void
}

const props = defineProps<ExtendedDataTableProps>()
const { table } = generateVueTable<Customer>(props)

const customerDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="customer" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton
          variant="destructive"
          size="icon"
          class="size-8"
          aria-label="Delete selected customers"
          title="Delete selected customers"
          @click="customerDeleteBatchOpen = true"
        >
          <Trash2Icon />
          <span class="sr-only">Delete selected customers</span>
        </UiButton>
      </UiTooltipTrigger>
      <UiTooltipContent>
        <p>Delete selected customers</p>
      </UiTooltipContent>
    </UiTooltip>

    <CustomerDeleteBatch v-model:open="customerDeleteBatchOpen" :table />
  </BulkActions>

  <DataTable :columns :table :data :loading>
    <template #toolbar>
      <DataTableToolbar
        :table="table"
        :filters="filters || {}"
        :on-filters-change="onFiltersChange || (() => {})"
        :on-clear-filters="onClearFilters || (() => {})"
        class="w-full overflow-x-auto"
      />
    </template>
  </DataTable>
</template>
