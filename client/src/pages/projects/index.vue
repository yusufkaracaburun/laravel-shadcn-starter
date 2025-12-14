<script setup lang="ts">
import { LayoutGrid, List, Grid3x3 } from 'lucide-vue-next'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useProjects } from '@/composables/use-projects'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import ProjectCreate from './components/project-create.vue'
import ProjectImport from './components/project-import.vue'
import ProjectsKanbanWrapper from './components/projects-kanban-wrapper.vue'
import ProjectsCardGrid from './components/projects-card-grid.vue'

type ViewMode = 'table' | 'kanban' | 'card'

const viewMode = ref<ViewMode>('table')
const { loading, projects, serverPagination, sorting, onSortingChange } = useProjects()
</script>

<template>
  <Page title="Projects" description="Projects description" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="projects_table-view_button"
              @click="viewMode = 'table'"
            >
              <List class="size-4" />
              <span class="sr-only">Table view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Table view</p>
          </TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'card' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="projects_card-view_button"
              @click="viewMode = 'card'"
            >
              <Grid3x3 class="size-4" />
              <span class="sr-only">Card view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Card view</p>
          </TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'kanban' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="projects_kanban-view_button"
              @click="viewMode = 'kanban'"
            >
              <LayoutGrid class="size-4" />
              <span class="sr-only">Kanban view</span>
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <p>Kanban view</p>
          </TooltipContent>
        </Tooltip>
        <ProjectImport />
        <ProjectCreate />
      </div>
    </template>
    <div v-if="viewMode === 'table'" class="overflow-x-auto">
      <DataTable
        :loading="loading"
        :data="projects"
        :columns="columns"
        :server-pagination="serverPagination"
        :sorting="sorting"
        :on-sorting-change="onSortingChange"
      />
    </div>
    <div v-else-if="viewMode === 'card'">
      <ProjectsCardGrid :projects="projects" :loading="loading" />
    </div>
    <div v-else-if="viewMode === 'kanban'" class="h-full">
      <ProjectsKanbanWrapper :key="`kanban-${projects.length}`" :projects="projects" />
    </div>
  </Page>
</template>

