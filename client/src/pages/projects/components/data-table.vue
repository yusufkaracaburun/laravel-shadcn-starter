<script setup lang="ts">
import { Trash2Icon } from 'lucide-vue-next'

import type { DataTableProps } from '@/components/data-table/types'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import type { Project } from '../data/schema'

import DataTableToolbar from './data-table-toolbar.vue'
import ProjectDeleteBatch from './project-delete-batch.vue'

const props = defineProps<DataTableProps<Project>>()
const table = generateVueTable<Project>(props)

const projectDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="project" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton
          variant="destructive"
          size="icon"
          class="size-8"
          aria-label="Delete selected projects"
          title="Delete selected projects"
          @click="projectDeleteBatchOpen = true"
        >
          <Trash2Icon />
          <span class="sr-only">Delete selected projects</span>
        </UiButton>
      </UiTooltipTrigger>
      <UiTooltipContent>
        <p>Delete selected projects</p>
      </UiTooltipContent>
    </UiTooltip>

    <ProjectDeleteBatch v-model:open="projectDeleteBatchOpen" :table />
  </BulkActions>

  <DataTable :columns :table :data :loading>
    <template #toolbar>
      <DataTableToolbar :table="table" class="w-full overflow-x-auto" />
    </template>
  </DataTable>
</template>

