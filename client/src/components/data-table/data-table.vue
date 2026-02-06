<script setup lang="ts" generic="T">
import type { Table as VueTable } from '@tanstack/vue-table'

import { FlexRender } from '@tanstack/vue-table'

import DataTableLoading from '@/components/data-table/table-loading.vue'
import DataTablePagination from '@/components/data-table/table-pagination.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

import type { IDataTableProps } from './types'

import NoResultFound from '../no-result-found.vue'

const props = defineProps<
  IDataTableProps<T> & {
    table: VueTable<T>
  }
>()

const emit = defineEmits<{
  rowClick: [row: T]
}>()

function handleRowClick(event: MouseEvent, row: T) {
  // Don't trigger row click if clicking on a button, link, or checkbox
  const target = event.target as HTMLElement
  if (
    target.closest('button') ||
    target.closest('a') ||
    target.closest('input[type="checkbox"]') ||
    target.closest('[role="button"]')
  ) {
    return
  }
  emit('rowClick', row)
}
</script>

<template>
  <div class="space-y-4">
    <slot name="toolbar" />

    <div class="border rounded-md">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody v-if="!loading">
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
              class="cursor-pointer"
              @click="(event) => handleRowClick(event, row.original)"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              <NoResultFound />
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      <DataTableLoading v-if="loading" />
    </div>

    <DataTablePagination
      v-if="!loading"
      :table="table"
      :server-pagination="props.serverPagination"
    />
  </div>
</template>
