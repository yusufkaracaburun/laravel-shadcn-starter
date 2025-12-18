import type { ColumnDef } from '@tanstack/vue-table'

import dayjs from 'dayjs'
import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Badge } from '@/components/ui/badge'
import { StatusBadge } from '@/components/ui/status-badge'

import type { Task } from '../data/schema'

import { labels, priorities, statuses } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

function getLabelVariant(label: string): 'default' | 'secondary' | 'destructive' | 'outline' {
  const normalizedLabel = label.toLowerCase()
  if (normalizedLabel === 'bug') return 'destructive'
  if (normalizedLabel === 'feature') return 'default'
  if (normalizedLabel === 'documentation') return 'secondary'
  return 'outline'
}

export const columns: ColumnDef<Task>[] = [
  SelectColumn as ColumnDef<Task>,
  {
    accessorKey: 'id',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Task' }),
    cell: ({ row }) => h('div', { class: 'w-20' }, row.getValue('id')),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Title' }),

    cell: ({ row }) => {
      return h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('title'))
    },
  },
  {
    accessorKey: 'description',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Description' }),
    cell: ({ row }) => {
      const description = row.original.description
      if (!description) return h('span', { class: 'text-muted-foreground' }, '—')
      return h(
        'span',
        { class: 'max-w-[300px] truncate text-sm text-muted-foreground' },
        description,
      )
    },
    enableSorting: false,
  },
  {
    accessorKey: 'labels',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Labels' }),
    cell: ({ row }) => {
      const taskLabels = row.original.labels || []
      if (taskLabels.length === 0) return h('span', { class: 'text-muted-foreground' }, '—')
      return h(
        'div',
        { class: 'flex flex-wrap gap-1' },
        taskLabels.map((label: string) =>
          h(
            Badge,
            { variant: getLabelVariant(label) },
            () => label.charAt(0).toUpperCase() + label.slice(1),
          ),
        ),
      )
    },
    enableSorting: false,
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Status' }),

    cell: ({ row }) => {
      const status = statuses.find((status) => status.value === row.getValue('status'))

      if (!status) return null

      return h(StatusBadge, {
        status: status.value,
        type: 'task',
        icon: status.icon,
        label: status.label,
      })
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'priority',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Priority' }),
    cell: ({ row }) => {
      const priority = priorities.find((priority) => priority.value === row.getValue('priority'))

      if (!priority) return null

      return h('div', { class: 'flex items-center gap-2' }, [
        priority.icon && h(priority.icon, { class: `h-4 w-4 ${priority.color}` }),
        h('span', { class: priority.color }, priority.label),
      ])
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'dueDate',
    header: ({ column }) => h(DataTableColumnHeader<Task>, { column, title: 'Due Date' }),
    cell: ({ row }) => {
      const dueDate = row.original.dueDate
      if (!dueDate) return h('span', { class: 'text-muted-foreground' }, '—')
      const date = typeof dueDate === 'string' ? dayjs(dueDate) : dayjs(dueDate)
      return h('span', { class: 'text-sm' }, date.format('MMM D, YYYY'))
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
