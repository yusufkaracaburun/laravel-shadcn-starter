<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { Grid3x3Icon, ListIcon } from '@/composables/use-icons'
import { useInvoices } from '@/composables/use-invoices'

import { getInvoiceColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import InvoiceCreate from './components/invoice-create.vue'
import InvoicesCardGrid from './components/invoices-card-grid.vue'

type TViewMode = 'table' | 'card'

const router = useRouter()
const columns = getInvoiceColumns(router)

const viewMode = ref<TViewMode>('table')
const {
  loading,
  invoices,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useInvoices()
</script>

<template>
  <Page
    title="Invoices"
    description="Manage your invoices"
    sticky
    data-testid="invoices_page"
  >
    <template #actions>
      <div class="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="invoices_table-view_button"
              @click="viewMode = 'table'"
            >
              <ListIcon class="size-4" />
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
              data-testid="invoices_card-view_button"
              @click="viewMode = 'card'"
            >
              <Grid3x3Icon class="size-4" />
              <span class="sr-only">Card view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Card view</p>
          </TooltipContent>
        </Tooltip>
        <InvoiceCreate />
      </div>
    </template>
    <div v-if="viewMode === 'table'" class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="invoices"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sort"
        :on-sorting-change="onSortingChange"
        :filters="filter"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="clearFilters"
      />
    </div>
    <div v-else-if="viewMode === 'card'">
      <InvoicesCardGrid :invoices="invoices" :loading="loading" />
    </div>
  </Page>
</template>
