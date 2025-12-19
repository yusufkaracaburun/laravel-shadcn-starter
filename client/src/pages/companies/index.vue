<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useCompanies } from '@/composables/use-companies'

import { columns } from './components/columns'
import CompanyCreate from './components/company-create.vue'
import CompanyImport from './components/company-import.vue'
import DataTable from './components/data-table.vue'

const { loading, companies, serverPagination, sorting, onSortingChange }
  = useCompanies()
</script>

<template>
  <Page title="Companies" description="Companies description" sticky>
    <template #actions>
      <CompanyImport />
      <CompanyCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="companies"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sorting"
        :on-sorting-change="onSortingChange"
      />
    </div>
  </Page>
</template>
