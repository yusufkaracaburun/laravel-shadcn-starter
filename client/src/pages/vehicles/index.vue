<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Page from '@/components/global-layout/basic-page-with-sidebar.vue'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

import { getVehicleColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import VehicleCreate from './components/vehicle-create-dialog.vue'
import VehicleSidebar from './components/vehicle-sidebar.vue'

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

const selectedVehicle = ref<IVehicle | null>(null)

function handleRowClick(vehicle: IVehicle) {
  selectedVehicle.value = vehicle
}

function handleCloseSidebar() {
  selectedVehicle.value = null
}

const hasSelectedVehicle = computed(() => selectedVehicle.value !== null)
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
    <div class="overflow-x-auto" data-testid="vehicles_table">
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
        @row-click="handleRowClick"
      />
    </div>
    <template v-if="hasSelectedVehicle && selectedVehicle" #sidebar>
      <VehicleSidebar :vehicle="selectedVehicle" @close="handleCloseSidebar" />
    </template>
  </Page>
</template>
