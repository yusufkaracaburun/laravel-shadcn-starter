<script setup lang="ts">
import { Trash2Icon } from '@/composables/use-icons.composable'

import type { IDataTableProps } from '@/components/data-table/types'
import type {
  ICustomer,
  ICustomerFilters,
} from '@/pages/customers/models/customers'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import CustomerDeleteBatch from './customer-delete-batch.vue'
import DataTableToolbar from './data-table-toolbar.vue'

const props = defineProps<IDataTableProps<ICustomer, ICustomerFilters>>()
const { table } = generateVueTable<ICustomer, ICustomerFilters>(props)

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

  <DataTable
    :table="table"
    :columns="columns"
    :loading="loading"
    :server-pagination="serverPagination"
  >
    <template #toolbar>
      <DataTableToolbar
        :table="table"
        :filters="filters"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="onClearFilters"
        class="w-full overflow-x-auto"
      />
    </template>
  </DataTable>
</template>
