<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'

import { columns } from './components/columns'
import CompanyCreate from './components/company-create.vue'
import CompanyImport from './components/company-import.vue'
import DataTable from './components/data-table.vue'
import companies from './data/companies.json'
</script>

<template>
  <Page title="Companies" description="Companies description" sticky>
    <template #actions>
      <CompanyImport />
      <CompanyCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable :data="companies" :columns="columns" />
    </div>
  </Page>
</template>
