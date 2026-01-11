<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useTimesheets } from '@/pages/timesheets/composables/use-timesheets.composable'

import { getTimesheetsColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import TimesheetCreate from './components/timesheet-create-dialog.vue'

const columns = getTimesheetsColumns()

const {
  loading,
  timesheets,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useTimesheets()
</script>

<template>
  <Page
    title="Timesheets"
    description="Manage your timesheet entries"
    sticky
    data-testid="timesheets_page"
  >
    <template #actions>
      <TimesheetCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="timesheets"
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
