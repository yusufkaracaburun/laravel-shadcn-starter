<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useCompanies } from '@/composables/use-companies.composable'

import { columns } from './components/columns'
import CompanyCreateDialog from './components/company-create-dialog.vue'
import CompanyImport from './components/company-import.vue'
import DataTable from './components/data-table.vue'

const {
  loading,
  companies,
  serverPagination,
  sorting,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useCompanies()
</script>

<template>
  <Page
    title="Companies"
    description="Companies description"
    sticky
    data-testid="companies_page"
  >
    <template #actions>
      <CompanyImport />
      <CompanyCreateDialog />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="companies"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sorting"
        :on-sorting-change="onSortingChange"
        :filters="filter"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="clearFilters"
      />
    </div>
  </Page>
</template>
