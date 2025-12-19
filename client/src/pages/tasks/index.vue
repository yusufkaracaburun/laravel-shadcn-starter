<script setup lang="ts">
import { LayoutGrid, List } from 'lucide-vue-next'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'

import { columns } from './components/columns'
import DataTable from './components/data-table.vue'
import TaskCreate from './components/task-create.vue'
import TaskImport from './components/task-import.vue'
import TasksKanbanWrapper from './components/tasks-kanban-wrapper.vue'
import tasksData from './data/tasks.json'

type ViewMode = 'table' | 'kanban'

const viewMode = ref<ViewMode>('table')
const tasks = ref(tasksData)

function handleTasksUpdate(updatedTasks: typeof tasksData) {
  tasks.value = updatedTasks
}
</script>

<template>
  <Page
    title="Tasks"
    description="Tasks description"
    sticky
    data-testid="tasks_page"
  >
    <template #actions>
      <div class="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger as-child>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="tasks_table-view_button"
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
              :variant="viewMode === 'kanban' ? 'default' : 'outline'"
              size="icon"
              class="size-8"
              data-testid="tasks_kanban-view_button"
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
        <TaskImport />
        <TaskCreate />
      </div>
    </template>
    <div v-if="viewMode === 'table'" class="overflow-x-auto">
      <DataTable :data="tasks" :columns="columns" />
    </div>
    <div v-else-if="viewMode === 'kanban'" class="h-full">
      <TasksKanbanWrapper
        :key="`kanban-${tasks.length}`"
        :tasks="tasks"
        @update:tasks="handleTasksUpdate"
      />
    </div>
  </Page>
</template>
