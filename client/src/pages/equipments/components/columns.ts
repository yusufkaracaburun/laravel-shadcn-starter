import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IEquipment } from '../models/equipments'

import DataTableRowActions from './data-table-row-actions.vue'

// CSS class constants
const CELL_CLASSES = {
  NAME_CONTAINER: 'flex items-center gap-2',
  SERIAL_NUMBER_CONTAINER: 'flex items-center gap-2',
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
 * Helper function to create serial number cell with copy functionality
 */
function createSerialNumberCell(serialNumberValue: unknown) {
  const serialNumberStr =
    serialNumberValue && typeof serialNumberValue === 'string'
      ? serialNumberValue
      : ''

  if (!serialNumberStr) {
    return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '—')
  }

  return h('div', { class: CELL_CLASSES.SERIAL_NUMBER_CONTAINER }, [
    h('span', {}, serialNumberStr),
    h(Copy, {
      class: 'ml-2',
      size: 'sm',
      variant: 'ghost',
      content: serialNumberStr,
    }),
  ])
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
 * This is the single source of truth for equipments columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IEquipment>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<IEquipment>,
    {
      accessorKey: 'name',
      header: ({ column }) =>
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.name') || 'Name',
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
      accessorKey: 'type',
      header: ({ column }) =>
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.type') || 'Type',
        }),
      cell: ({ row }) => {
        const type = row.getValue('type')
        return h(
          'div',
          {},
          (typeof type === 'string' ? type : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'model',
      header: ({ column }) =>
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.model') || 'Model',
        }),
      cell: ({ row }) => {
        const model = row.getValue('model')
        return h(
          'div',
          {},
          (typeof model === 'string' ? model : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'serial_number',
      header: ({ column }) =>
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.serialNumber') || 'Serial Number',
        }),
      cell: ({ row }) => {
        const serialNumber = row.getValue('serial_number')
        return createSerialNumberCell(serialNumber)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'status',
      header: ({ column }) =>
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.status') || 'Status',
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
        h(DataTableColumnHeader<IEquipment>, {
          column,
          title: t('equipment.columns.createdAt') || 'Created At',
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
 * Factory function to create equipments table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getEquipmentsColumns(): ColumnDef<IEquipment>[] {
  return createColumns()
}
