<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useItems } from '@/pages/items/composables/use-items.composable'

import { getItemColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import ItemCreateDialog from './components/item-create-dialog.vue'

const columns = getItemColumns()

const {
  loading,
  items,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useItems()
</script>

<template>
  <Page
    title="Items"
    description="Items description"
    sticky
    data-testid="items_page"
  >
    <template #actions>
      <ItemCreateDialog />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="items"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sort"
        :on-sorting-change="onSortingChange"
        :filters="filter"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="clearFilters"
      />
    </div>
  </Page>
</template>
