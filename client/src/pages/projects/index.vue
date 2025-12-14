<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useProjects } from '@/composables/use-projects'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import ProjectCreate from './components/project-create.vue'
import ProjectImport from './components/project-import.vue'

const { loading, projects, serverPagination, sorting, onSortingChange } = useProjects()
</script>

<template>
  <Page title="Projects" description="Projects description" sticky>
    <template #actions>
      <ProjectImport />
      <ProjectCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="projects"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sorting"
        :on-sorting-change="onSortingChange"
      />
    </div>
  </Page>
</template>

