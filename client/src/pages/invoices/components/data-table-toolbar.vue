<script setup lang="ts">
import type { IDataTableToolbarProps } from '@/components/data-table/types'
import type { IInvoice, IInvoiceFilters } from '@/pages/invoices/models/invoice'

import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { XIcon } from '@/composables/use-icons'
import InvoicesFilter from '@/pages/invoices/components/invoices-filter.vue'

const props = defineProps<IDataTableToolbarProps<IInvoice, IInvoiceFilters>>()

const invoiceNumberColumn = computed(() =>
  props.table.getColumn('invoice_number'),
)

const searchValue = computed({
  get: () => (invoiceNumberColumn.value?.getFilterValue() as string) ?? '',
  set: (value: string) => invoiceNumberColumn.value?.setFilterValue(value),
})

const isFiltered = computed(() => {
  return (
    props.table.getState().columnFilters.length > 0 ||
    (props.filters && Object.keys(props.filters).length > 0)
  )
})
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <InvoicesFilter
        :filters="filters"
        :on-filters-change="onFiltersChange"
        :on-clear="onClearFilters"
      />
      <Input
        v-model="searchValue"
        :placeholder="$t('invoices.search')"
        class="h-8 w-[150px] lg:w-[250px]"
      />

      <Button
        v-if="isFiltered"
        class="h-8 px-2 lg:px-3"
        size="sm"
        @click="
          () => {
            table.resetColumnFilters()
            onClearFilters()
          }
        "
      >
        {{ $t('invoices.actions.reset') }}
        <XIcon class="size-4" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>
