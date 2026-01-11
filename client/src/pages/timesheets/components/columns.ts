import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { ITimesheet } from '../models/timesheets'

import DataTableRowActions from './data-table-row-actions.vue'

// CSS class constants
const CELL_CLASSES = {
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create date cell
 */
function createDateCell(dateValue: unknown) {
  const dateValueString = dateValue as string | null | undefined
  return h(
    'div',
    { class: CELL_CLASSES.CREATED_AT_CELL },
    formatDate(dateValueString ?? ''),
  )
}

/**
 * Creates the column definitions array
 * This is the single source of truth for timesheet columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<ITimesheet>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<ITimesheet>,
    {
      accessorKey: 'date',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.date') || 'Date',
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('date')
        return createDateCell(dateValue)
      },
      enableSorting: true,
      enableHiding: false,
      enableResizing: true,
    },
    {
      accessorKey: 'user_id',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.user') || 'User',
        }),
      cell: ({ row }) => {
        const userId = row.getValue('user_id')
        return h(
          'div',
          {},
          (typeof userId === 'number' ? userId.toString() : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'project_id',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.project') || 'Project',
        }),
      cell: ({ row }) => {
        const projectId = row.getValue('project_id')
        return h(
          'div',
          {},
          (typeof projectId === 'number' ? projectId.toString() : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'task_id',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.task') || 'Task',
        }),
      cell: ({ row }) => {
        const taskId = row.getValue('task_id')
        return h(
          'div',
          {},
          (typeof taskId === 'number' ? taskId.toString() : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'hours',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.hours') || 'Hours',
        }),
      cell: ({ row }) => {
        const hours = row.getValue('hours')
        return h(
          'div',
          {},
          (typeof hours === 'number' ? hours.toFixed(2) : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'description',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.description') || 'Description',
        }),
      cell: ({ row }) => {
        const description = row.getValue('description')
        return h(
          'div',
          {},
          (typeof description === 'string' ? description : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'status',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.status') || 'Status',
        }),
      cell: ({ row }) => {
        const status = row.getValue('status')
        return h(
          'div',
          {},
          (typeof status === 'string' ? status : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITimesheet>, {
          column,
          title: t('timesheets.columns.createdAt') || 'Created At',
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('created_at')
        return createDateCell(dateValue)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      id: 'actions',
      cell: ({ row }) => h(DataTableRowActions, { row }),
    },
  ]
}

/**
 * Factory function to create timesheet table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getTimesheetsColumns(): ColumnDef<ITimesheet>[] {
  return createColumns()
}
