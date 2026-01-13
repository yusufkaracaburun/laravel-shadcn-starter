<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useProducts } from '@/pages/products/composables/use-products.composable'

import { getProductColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import ProductCreateDialog from './components/product-create-dialog.vue'

const columns = getProductColumns()

const {
  loading,
  products,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useProducts()
</script>

<template>
  <Page
    title="Products"
    description="Products description"
    sticky
    data-testid="products_page"
  >
    <template #actions>
      <ProductCreateDialog />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="products"
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
