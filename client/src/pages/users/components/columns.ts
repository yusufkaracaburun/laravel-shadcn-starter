import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import Badge from '@/components/ui/badge/Badge.vue'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IRole, IUser } from '../models/users'

import DataTableRowActions from './data-table-row-actions.vue'
import UserNameCell from './user-name-cell.vue'

type IUserWithRoles = IUser & {
  roles?: IRole[]
}

// CSS class constants
const CELL_CLASSES = {
  EMAIL_CONTAINER: 'flex items-center gap-2',
  ROLES_CONTAINER: 'flex flex-wrap gap-1',
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create email cell with copy functionality
 */
function createEmailCell(emailValue: unknown) {
  const emailStr =
    emailValue && typeof emailValue === 'string' ? emailValue : ''

  return h('div', { class: CELL_CLASSES.EMAIL_CONTAINER }, [
    h('span', {}, emailStr || '—'),
    h(Copy, {
      class: 'ml-2',
      size: 'sm',
      variant: 'ghost',
      content: emailStr,
    }),
  ])
}

/**
 * Helper function to create roles cell
 */
function createRolesCell(user: IUserWithRoles) {
  const roles = user.roles || []

  if (roles.length === 0) {
    return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '—')
  }

  return h(
    'div',
    { class: CELL_CLASSES.ROLES_CONTAINER },
    roles.map((role: { name: string }) =>
      h(Badge, { variant: 'outline', key: role.name }, () => role.name),
    ),
  )
}

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
 * This is the single source of truth for user columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IUser>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<IUser>,
    {
      accessorKey: 'name',
      header: ({ column }) =>
        h(DataTableColumnHeader<IUser>, {
          column,
          title: t('users.columns.name'),
        }),
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
      header: ({ column }) =>
        h(DataTableColumnHeader<IUser>, {
          column,
          title: t('users.columns.email'),
        }),
      cell: ({ row }) => {
        const email = row.getValue('email')
        return createEmailCell(email)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      id: 'roles',
      accessorFn: (row: IUser) => {
        // Extract role names from the roles array
        const userWithRoles = row as IUserWithRoles
        if (!userWithRoles.roles || !Array.isArray(userWithRoles.roles)) {
          return []
        }
        return userWithRoles.roles.map((role: { name: string }) => role.name)
      },
      header: ({ column }) =>
        h(DataTableColumnHeader<IUser>, {
          column,
          title: t('users.columns.roles') || 'Roles',
        }),
      cell: ({ row }) => {
        const user = row.original as IUserWithRoles
        return createRolesCell(user)
      },
      filterFn: (row, _id, value) => {
        // value is an array of selected role names
        if (!value || value.length === 0) {
          return true
        }
        const userWithRoles = row.original as IUserWithRoles
        const userRoles = userWithRoles.roles || []
        const roleNames = new Set(
          userRoles.map((role: { name: string }) => role.name),
        )
        // Check if any of the selected roles match the user's roles
        return value.some((selectedRole: string) => roleNames.has(selectedRole))
      },
      enableSorting: false,
      enableResizing: true,
    },
    {
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<IUser>, {
          column,
          title: t('users.columns.createdAt'),
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
 * Factory function to create user table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getUserColumns(): ColumnDef<IUser>[] {
  return createColumns()
}
