<script setup lang="ts">
import { Trash2Icon } from 'lucide-vue-next'

import type { IDataTableProps } from '@/components/data-table/types'
import type { IInvoiceFilters } from '@/pages/invoices/models/invoice'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import type { TInvoice } from '../data/schema'

import DataTableToolbar from './data-table-toolbar.vue'
import InvoiceDeleteBatch from './invoice-delete-batch.vue'

interface IExtendedDataTableProps extends IDataTableProps<TInvoice> {
  filters?: IInvoiceFilters
  onFiltersChange?: (filters: IInvoiceFilters) => void
  onClearFilters?: () => void
}

const props = defineProps<IExtendedDataTableProps>()
const { table } = generateVueTable<TInvoice>(props)

const invoiceDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="invoice" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton variant="destructive" size="icon" class="size-8" aria-label="Delete selected invoices"
          title="Delete selected invoices" @click="invoiceDeleteBatchOpen = true">
          <Trash2Icon />
          <span class="sr-only">Delete selected invoices</span>
        </UiButton>
      </UiTooltipTrigger>
      <UiTooltipContent>
        <p>Delete selected invoices</p>
      </UiTooltipContent>
    </UiTooltip>

    <InvoiceDeleteBatch v-model:open="invoiceDeleteBatchOpen" :table />
  </BulkActions>

  <DataTable :columns :table :data :loading>
    <!-- <template #toolbar>
      <DataTableToolbar
        :table="table"
        :filters="filters || {}"
        :on-filters-change="onFiltersChange || (() => {})"
        :on-clear-filters="onClearFilters || (() => {})"
        class="w-full overflow-x-auto"
      />
    </template> -->
  </DataTable>
</template>
