import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { StatusBadge } from '@/components/ui/status-badge'

import type { Project } from '../data/schema'

import { categories, statuses } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<Project>[] = [
  SelectColumn as ColumnDef<Project>,
  {
    accessorKey: 'id',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Project ID' }),
    cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const project = row.original
      const router = useRouter()
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '') || ''

      return h(
        'button',
        {
          class: 'max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({ name: '/projects/[id]', params: { id: project.id.toString() } })
          },
        },
        name,
      )
    },
    enableSorting: true,
    enableResizing: true,
    enableHiding: false,
  },
  {
    accessorKey: 'category',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Category' }),
    cell: ({ row }) => {
      const category = categories.find(category => category.value === row.getValue('category'))

      return h('div', { class: 'flex items-center' }, [
        category ? h(Badge, { variant: 'outline' }, () => category.label) : null,
      ])
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Status' }),
    cell: ({ row }) => {
      const status = statuses.find(status => status.value === row.getValue('status'))

      if (!status)
        return null

      return h(StatusBadge, {
        status: status.value,
        type: 'project',
        icon: status.icon,
        label: status.label,
      })
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'progress',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Progress' }),
    cell: ({ row }) => {
      const progress = row.getValue('progress') as number
      return h('div', { class: 'flex w-[120px] items-center gap-2' }, [
        h(Progress, { modelValue: progress, class: 'h-2 w-full' }),
        h('span', { class: 'text-sm text-muted-foreground' }, `${progress}%`),
      ])
    },
  },
  {
    accessorKey: 'startDate',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'Start Date' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('startDate') as string | null | undefined
      if (!dateValue) {
        return h('div', { class: 'w-[100px] text-muted-foreground' }, '-')
      }
      const date = new Date(dateValue)
      return h('div', { class: 'w-[100px]' }, date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }))
    },
  },
  {
    accessorKey: 'endDate',
    header: ({ column }) => h(DataTableColumnHeader<Project>, { column, title: 'End Date' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('endDate') as string | null | undefined
      if (!dateValue) {
        return h('div', { class: 'w-[100px] text-muted-foreground' }, '-')
      }
      const date = new Date(dateValue)
      return h('div', { class: 'w-[100px]' }, date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }))
    },
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
