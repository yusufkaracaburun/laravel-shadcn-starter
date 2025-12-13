<script setup lang="ts">
import { Trash2Icon } from 'lucide-vue-next'

import type { DataTableProps } from '@/components/data-table/types'

import BulkActions from '@/components/data-table/bulk-actions.vue'
import DataTable from '@/components/data-table/data-table.vue'
import { generateVueTable } from '@/components/data-table/use-generate-vue-table'

import type { Company } from '../data/schema'

import CompanyDeleteBatch from './company-delete-batch.vue'
import DataTableToolbar from './data-table-toolbar.vue'

const props = defineProps<DataTableProps<Company>>()
const { table } = generateVueTable<Company>(props)

const companyDeleteBatchOpen = ref(false)
</script>

<template>
  <BulkActions entity-name="company" :table>
    <UiTooltip>
      <UiTooltipTrigger as-child>
        <UiButton
          variant="destructive"
          size="icon"
          class="size-8"
          aria-label="Delete selected companies"
          title="Delete selected companies"
          @click="companyDeleteBatchOpen = true"
        >
          <Trash2Icon />
          <span class="sr-only">Delete selected companies</span>
        </UiButton>
      </UiTooltipTrigger>
      <UiTooltipContent>
        <p>Delete selected companies</p>
      </UiTooltipContent>
    </UiTooltip>

    <CompanyDeleteBatch v-model:open="companyDeleteBatchOpen" :table />
  </BulkActions>

  <DataTable :columns :table :data :loading>
    <template #toolbar>
      <DataTableToolbar :table="table" class="w-full overflow-x-auto" />
    </template>
  </DataTable>
</template>
