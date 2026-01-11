<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

import { getVehicleColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import VehicleCreate from './components/vehicle-create-dialog.vue'

const columns = getVehicleColumns()

const {
  loading,
  vehicles,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useVehicles()
</script>

<template>
  <Page
    title="Vehicles"
    description="Manage your fleet of vehicles"
    sticky
    data-testid="vehicles_page"
  >
    <template #actions>
      <VehicleCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="vehicles"
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
