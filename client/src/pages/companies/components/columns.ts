import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import type { Company } from '@/services/companies.service'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Badge } from '@/components/ui/badge'

import { employeeSizes, industries, statuses } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<Company>[] = [
  SelectColumn as ColumnDef<Company>,
  {
    accessorKey: 'id',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Company ID' }),
    cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
    enableSorting: true,
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Name' }),
    cell: ({ row }) => {
      return h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('name'))
    },
    enableSorting: true,
    enableResizing: true,
  },
  {
    accessorKey: 'industry',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Industry' }),
    cell: ({ row }) => {
      const industry = industries.find((industry) => industry.value === row.getValue('industry'))

      return h('div', { class: 'flex items-center' }, [
        industry ? h(Badge, { variant: 'outline' }, () => industry.label) : null,
      ])
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'email',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Email' }),
    cell: ({ row }) => h('div', { class: 'max-w-[300px] truncate' }, row.getValue('email')),
    enableSorting: true,
    enableResizing: true,
  },
  {
    accessorKey: 'phone',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Phone' }),
    cell: ({ row }) => h('div', { class: 'w-[120px]' }, row.getValue('phone')),
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Status' }),
    cell: ({ row }) => {
      const status = statuses.find((status) => status.value === row.getValue('status'))

      if (!status) return null

      return h(
        Badge,
        {
          class: `flex w-fit items-center gap-2 ${status.color}`,
          variant: 'secondary',
        },
        () => [
          status.icon && h(status.icon, { class: 'h-3 w-3' }),
          h('span', status.label),
        ],
      )
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'employees',
    header: ({ column }) => h(DataTableColumnHeader<Company>, { column, title: 'Employees' }),
    cell: ({ row }) => {
      const employeeSize = employeeSizes.find(
        (size) => size.value === row.getValue('employees'),
      )

      if (!employeeSize) return null

      return h('div', { class: 'flex items-center' }, [h('span', {}, employeeSize.label)])
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]

