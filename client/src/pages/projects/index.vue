<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import ProjectCreate from './components/project-create.vue'
import ProjectImport from './components/project-import.vue'
import projects from './data/projects.json'
</script>

<template>
  <Page title="Projects" description="Projects description" sticky>
    <template #actions>
      <ProjectImport />
      <ProjectCreate />
    </template>
    <div class="overflow-x-auto">
      <DataTable :data="projects" :columns="columns" />
    </div>
  </Page>
</template>

