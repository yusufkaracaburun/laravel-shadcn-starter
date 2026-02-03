<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Page from '@/components/global-layout/index-layout.vue'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

import { getVehicleColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import VehicleCreate from './components/vehicle-create-dialog.vue'
import VehicleSheet from './components/vehicle-sheet.vue'

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
const sidebarOpen = ref(false)

function handleRowClick(vehicle: IVehicle) {
  selectedVehicle.value = vehicle
  sidebarOpen.value = true
}

function handleCloseSidebar() {
  selectedVehicle.value = null
  sidebarOpen.value = false
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
    <template v-if="hasSelectedVehicle && selectedVehicle" #row-details>
      <VehicleSheet
        :vehicle="selectedVehicle"
        :open="sidebarOpen"
        @close="handleCloseSidebar"
      />
    </template>
  </Page>
</template>
