<script setup lang="ts">
import { Grid3x3, List } from 'lucide-vue-next'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useCustomers } from '@/composables/use-customers'

import { columns } from './components/columns'
import CustomerCreate from './components/customer-create.vue'
import CustomersCardGrid from './components/customers-card-grid.vue'
import DataTable from './components/data-table.vue'

type ViewMode = 'table' | 'card'

const viewMode = ref<ViewMode>('table')
const {
  loading,
  customers,
  serverPagination,
  sorting,
  onSortingChange,
  filters,
  onFiltersChange,
  clearFilters,
} = useCustomers()
</script>

<template>
  <Page title="Customers" description="Manage your customers" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="customers_table-view_button"
              @click="viewMode = 'table'"
            >
              <List class="size-4" />
              <span class="sr-only">Table view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Table view</p>
          </TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'card' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="customers_card-view_button"
              @click="viewMode = 'card'"
            >
              <Grid3x3 class="size-4" />
              <span class="sr-only">Card view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Card view</p>
          </TooltipContent>
        </Tooltip>
        <CustomerCreate />
      </div>
    </template>
    <div v-if="viewMode === 'table'" class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="customers"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sorting"
        :on-sorting-change="onSortingChange"
        :filters="filters"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="clearFilters"
      />
    </div>
    <div v-else-if="viewMode === 'card'">
      <CustomersCardGrid :customers="customers" :loading="loading" />
    </div>
  </Page>
</template>

