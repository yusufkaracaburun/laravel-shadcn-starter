<script setup lang="ts">
import { computed, ref } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import Page from '@/components/global-layout/index-layout.vue'
import { useCustomers } from '@/pages/customers/composables/use-customers.composable'

import { columns } from './components/columns'
import CustomerCreate from './components/customer-create.vue'
import CustomerDetailsSheet from './components/customer-details-sheet.vue'
import DataTable from './components/data-table.vue'

const {
  loading,
  customers,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useCustomers()

const selectedCustomer = ref<ICustomer | null>(null)
const sidebarOpen = ref(false)

function handleRowClick(customer: ICustomer) {
  selectedCustomer.value = customer
  sidebarOpen.value = true
}

function handleCloseSidebar() {
  selectedCustomer.value = null
  sidebarOpen.value = false
}

const hasSelectedCustomer = computed(() => selectedCustomer.value !== null)
</script>

<template>
  <Page
    title="Customers"
    description="Manage your customers"
    sticky
    data-testid="customers_page"
  >
    <template #actions>
      <CustomerCreate />
    </template>
    <div class="overflow-x-auto" data-testid="customers_table">
      <DataTable
        :loading="loading"
        :data="customers"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sort"
        :on-sorting-change="onSortingChange"
        :filters="filter"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="clearFilters"
        @row-click="handleRowClick"
      />
    </div>
    <template v-if="hasSelectedCustomer && selectedCustomer" #row-details>
      <CustomerDetailsSheet
        :customer="selectedCustomer"
        :open="sidebarOpen"
        @close="handleCloseSidebar"
      />
    </template>
  </Page>
</template>
