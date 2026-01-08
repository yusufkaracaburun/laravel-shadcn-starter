<script setup lang="ts">
import type { IDataTableProps } from '@/components/data-table/types'
import type { IUserFilters } from '@/pages/users/models/users'
import type { User } from '@/services/users.service'

import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import DataTableToolbar from './data-table-toolbar.vue'

const props = defineProps<IDataTableProps<User, IUserFilters>>()
const { table } = generateVueTable<User, IUserFilters>(props)
</script>

<template>
  <DataTable
    :table="table"
    :columns="columns"
    :loading="loading"
    :server-pagination="serverPagination"
  >
    <template #toolbar>
      <DataTableToolbar
        :table="table"
        :filters="filters"
        :on-filters-change="onFiltersChange"
        :on-clear-filters="onClearFilters"
        class="w-full overflow-x-auto"
      />
    </template>
  </DataTable>
</template>
