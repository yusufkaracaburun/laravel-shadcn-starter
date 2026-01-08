<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'

import type { IServerPagination } from '@/components/data-table/types'
import type { IInvoice, IInvoiceFilters } from '@/pages/invoices/models/invoice'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'
import { Trash2Icon } from '@/composables/use-icons'
import DataTableToolbar from '@/pages/invoices/components/data-table-toolbar.vue'
import InvoiceDeleteBatch from '@/pages/invoices/components/invoice-delete-batch.vue'

interface IExtendedDataTableProps {
  loading?: boolean
  columns: ColumnDef<IInvoice, any>[]
  data: IInvoice[]
  serverPagination?: IServerPagination
  filter?: IInvoiceFilters
  onFiltersChange?: (filters: IInvoiceFilters) => void
  onClearFilters?: () => void
}

const props = defineProps<IExtendedDataTableProps>()
const { table } = generateVueTable<IInvoice>(props)

const invoiceDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="invoice" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton
          variant="destructive"
          size="icon"
          class="size-8"
          aria-label="Delete selected invoices"
          title="Delete selected invoices"
          @click="invoiceDeleteBatchOpen = true"
        >
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

  <DataTable
    :columns
    :table
    :data
    :loading
    :server-pagination="props.serverPagination"
  >
    <template #toolbar>
      <DataTableToolbar
        :table="table"
        :filters="filter"
        :on-filters-change="onFiltersChange || (() => {})"
        :on-clear-filters="onClearFilters || (() => {})"
        class="w-full overflow-x-auto"
      />
    </template>
  </DataTable>
</template>
