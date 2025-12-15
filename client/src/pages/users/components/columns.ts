import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import type { User } from '@/services/users.service'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import Badge from '@/components/ui/badge/Badge.vue'

import DataTableRowActions from './data-table-row-actions.vue'
import UserNameCell from './user-name-cell.vue'

export const columns: ColumnDef<User>[] = [
  SelectColumn as ColumnDef<User>,
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const user = row.original
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '—') || '—'

      return h(UserNameCell, { user, name })
    },
    enableSorting: true,
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
        h(Copy, { class: 'ml-2', size: 'sm', variant: 'ghost', content: emailStr }),
      ])
    },
    enableSorting: true,
    enableResizing: true,
  },

  {
    id: 'roles',
    accessorFn: (row) => {
      // Extract role names from the roles array
      if (!row.roles || !Array.isArray(row.roles)) {
        return []
      }
      return row.roles.map((role: { name: string }) => role.name)
    },
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Roles' }),
    cell: ({ row }) => {
      const user = row.original
      const roles = user.roles || []

      if (roles.length === 0) {
        return h('div', {}, '—')
      }

      return h(
        'div',
        { class: 'flex flex-wrap gap-1' },
        roles.map((role: { name: string }) =>
          h(Badge, { variant: 'outline', key: role.name }, () => role.name),
        ),
      )
    },
    filterFn: (row, _id, value) => {
      // value is an array of selected role names
      if (!value || value.length === 0) {
        return true
      }
      const userRoles = row.original.roles || []
      const roleNames = new Set(userRoles.map((role: { name: string }) => role.name))
      // Check if any of the selected roles match the user's roles
      return value.some((selectedRole: string) => roleNames.has(selectedRole))
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
    enableSorting: true,
    enableResizing: true,
  },

  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
