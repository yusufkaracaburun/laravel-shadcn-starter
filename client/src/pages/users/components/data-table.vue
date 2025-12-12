<script setup lang="ts">
import type { DataTableProps } from '@/components/data-table/types'
import type { User } from '@/services/users.service'

import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import DataTableToolbar from './data-table-toolbar.vue'

const props = defineProps<DataTableProps<User>>()

const { table, sorting } = generateVueTable<User>(props)

// Expose sorting state to parent component if needed
// The sorting state is managed internally by the table generator
// and will be used by the composable through the props.serverPagination flow
</script>

<template>
  <DataTable :columns :data :loading :table>
    <template #toolbar>
      <DataTableToolbar :table class="w-full overflow-x-auto" />
    </template>
  </DataTable>
</template>
