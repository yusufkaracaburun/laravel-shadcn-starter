<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useUsers } from '@/pages/users/composables/use-users.composable'

import { getUserColumns } from './components/columns'
import DataTable from './components/data-table.vue'
import UserCreate from './components/user-create-dialog.vue'
import UserInvite from './components/user-invite.vue'

const columns = getUserColumns()

const {
  loading,
  users,
  serverPagination,
  sort,
  onSortingChange,
  filter,
  onFiltersChange,
  clearFilters,
} = useUsers()
</script>

<template>
  <Page
    title="Users"
    description="Users description"
    sticky
    data-testid="users_page"
  >
    <template #actions>
      <!-- <UserInvite /> -->
      <UserCreate />
    </template>
    <div class="overflow-x-auto" data-testid="users_table">
      <DataTable
        :loading="loading"
        :data="users"
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
