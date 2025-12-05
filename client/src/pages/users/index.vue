<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useUsers } from '@/composables/use-users'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import UserCreate from './components/user-create.vue'
import UserInvite from './components/user-invite.vue'

const { loading, users, serverPagination } = useUsers()
</script>

<template>
  <Page title="Users" description="Users description" sticky>
    <template #actions>
      <UserInvite />
      <UserCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="users"
        :columns="columns"
        :server-pagination="serverPagination"
      />
    </div>
  </Page>
</template>
