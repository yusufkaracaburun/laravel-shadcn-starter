<script setup lang="ts">
import { Grid3x3, List } from 'lucide-vue-next'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useItems } from '@/composables/use-items.composable'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import ItemCreate from './components/item-create.vue'
import ItemsCardGrid from './components/items-card-grid.vue'

type ViewMode = 'table' | 'card'

const viewMode = ref<ViewMode>('table')
const {
  loading,
  items,
  serverPagination,
  sorting,
  onSortingChange,
  filters,
  onFiltersChange,
  clearFilters,
} = useItems()
</script>

<template>
  <Page title="Items" description="Items description" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="items_table-view_button"
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
              data-testid="items_card-view_button"
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
        <ItemCreate />
      </div>
    </template>
    <div v-if="viewMode === 'table'" class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="items"
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
      <ItemsCardGrid :items="items" :loading="loading" />
    </div>
  </Page>
</template>
