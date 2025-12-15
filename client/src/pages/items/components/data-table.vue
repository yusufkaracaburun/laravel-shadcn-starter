<script setup lang="ts">
import { Trash2Icon } from 'lucide-vue-next'

import type { DataTableProps } from '@/components/data-table/types'
import type { ItemFilters } from '@/services/items.service'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import type { Item } from '../data/schema'

import DataTableToolbar from './data-table-toolbar.vue'
import ItemDeleteBatch from './item-delete-batch.vue'

interface ExtendedDataTableProps extends DataTableProps<Item> {
  filters?: ItemFilters
  onFiltersChange?: (filters: ItemFilters) => void
  onClearFilters?: () => void
}

const props = defineProps<ExtendedDataTableProps>()
const { table } = generateVueTable<Item>(props)

const itemDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="item" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton
          variant="destructive"
          size="icon"
          class="size-8"
          aria-label="Delete selected items"
          title="Delete selected items"
          @click="itemDeleteBatchOpen = true"
        >
          <Trash2Icon />
          <span class="sr-only">Delete selected items</span>
        </UiButton>
      </UiTooltipTrigger>
      <UiTooltipContent>
        <p>Delete selected items</p>
      </UiTooltipContent>
    </UiTooltip>

    <ItemDeleteBatch v-model:open="itemDeleteBatchOpen" :table />
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
