import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import type { User } from '@/services/users.service'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import Badge from '@/components/ui/badge/Badge.vue'

import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<User>[] = [
  SelectColumn as ColumnDef<User>,
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Name' }),
    cell: ({ row }) => h('div', {}, row.getValue('name') || '—'),
    enableSorting: false,
    enableHiding: false,
    enableResizing: true,
  },

  {
    accessorKey: 'email',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Email' }),
    cell: ({ row }) => {
      const email = row.getValue('email')
      const emailStr = email && typeof email === 'string' ? email : ''
      return h('div', {}, [
        h('span', {}, emailStr || '—'),
        h(Copy, { class: 'ml-2', size: 'sm', content: emailStr }),
      ])
    },
    enableSorting: false,
    enableResizing: true,
  },

  {
    accessorKey: 'email_verified_at',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Verified' }),
    cell: ({ row }) => {
      const verifiedAt = row.getValue('email_verified_at')
      return h(
        Badge,
        { variant: verifiedAt ? 'default' : 'secondary' },
        () => verifiedAt ? 'Verified' : 'Unverified',
      )
    },
    enableSorting: false,
    enableResizing: true,
  },

  {
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const createdAt = row.getValue('created_at')
      if (!createdAt || typeof createdAt !== 'string') {
        return h('div', {}, '—')
      }
      const date = new Date(createdAt)
      return h('div', {}, date.toLocaleDateString())
    },
    enableSorting: false,
    enableResizing: true,
  },

  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
