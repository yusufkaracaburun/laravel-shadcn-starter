<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'

import { getEquipmentsColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import EquipmentsCreate from './components/equipments-create-dialog.vue'

const columns = getEquipmentsColumns()

const {
  loading,
  equipments,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useEquipments()
</script>

<template>
  <Page
    title="Equipment"
    description="Manage your equipment inventory"
    sticky
    data-testid="equipment_page"
  >
    <template #actions>
      <EquipmentsCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="equipments"
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
