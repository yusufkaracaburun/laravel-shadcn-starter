import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import Badge from '@/components/ui/badge/Badge.vue'
import { Copy } from '@/components/sva-ui/copy'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { ITeam } from '../models/teams'

import DataTableRowActions from './data-table-row-actions.vue'

// CSS class constants
const CELL_CLASSES = {
  NAME_CONTAINER: 'flex items-center gap-2',
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create name cell with copy functionality
 */
function createNameCell(nameValue: unknown) {
  const nameStr =
    nameValue && typeof nameValue === 'string'
      ? nameValue
      : ''

  return h('div', { class: CELL_CLASSES.NAME_CONTAINER }, [
    h('span', {}, nameStr || '—'),
    h(Copy, {
      class: 'ml-2',
      size: 'sm',
      variant: 'ghost',
      content: nameStr,
    }),
  ])
}

/**
 * Helper function to create personal team badge cell
 */
function createPersonalTeamBadge(personalTeamValue: unknown) {
  const isPersonalTeam = personalTeamValue === true

  if (!isPersonalTeam) {
    return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '—')
  }

  return h(
    Badge,
    { variant: 'secondary' },
    () => 'Personal',
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
 * This is the single source of truth for team columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<ITeam>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<ITeam>,
    {
      accessorKey: 'name',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITeam>, {
          column,
          title: t('teams.columns.name') || 'Name',
        }),
      cell: ({ row }) => {
        const name = row.getValue('name')
        return createNameCell(name)
      },
      enableSorting: true,
      enableHiding: false,
      enableResizing: true,
    },
    {
      accessorKey: 'personal_team',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITeam>, {
          column,
          title: t('teams.columns.personalTeam') || 'Type',
        }),
      cell: ({ row }) => {
        const personalTeam = row.getValue('personal_team')
        return createPersonalTeamBadge(personalTeam)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'user_id',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITeam>, {
          column,
          title: t('teams.columns.owner') || 'Owner ID',
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
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<ITeam>, {
          column,
          title: t('teams.columns.createdAt') || 'Created At',
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
 * Factory function to create team table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getTeamColumns(): ColumnDef<ITeam>[] {
  return createColumns()
}
