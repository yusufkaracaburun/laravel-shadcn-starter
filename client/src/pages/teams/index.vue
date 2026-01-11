<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useTeams } from '@/pages/teams/composables/use-teams.composable'

import { getTeamColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import TeamCreate from './components/team-create-dialog.vue'

const columns = getTeamColumns()

const {
  loading,
  teams,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useTeams()
</script>

<template>
  <Page
    title="Teams"
    description="Manage your teams"
    sticky
    data-testid="teams_page"
  >
    <template #actions>
      <TeamCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="teams"
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
