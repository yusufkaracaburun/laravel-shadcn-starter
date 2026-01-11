import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IVehicle } from '../models/vehicles'

import DataTableRowActions from './data-table-row-actions.vue'

// CSS class constants
const CELL_CLASSES = {
  LICENSE_PLATE_CONTAINER: 'flex items-center gap-2',
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create license plate cell with copy functionality
 */
function createLicensePlateCell(licensePlateValue: unknown) {
  const licensePlateStr =
    licensePlateValue && typeof licensePlateValue === 'string'
      ? licensePlateValue
      : ''

  return h('div', { class: CELL_CLASSES.LICENSE_PLATE_CONTAINER }, [
    h('span', {}, licensePlateStr || '—'),
    h(Copy, {
      class: 'ml-2',
      size: 'sm',
      variant: 'ghost',
      content: licensePlateStr,
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
 * This is the single source of truth for vehicle columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IVehicle>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<IVehicle>,
    {
      accessorKey: 'license_plate',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.licensePlate') || 'License Plate',
        }),
      cell: ({ row }) => {
        const licensePlate = row.getValue('license_plate')
        return createLicensePlateCell(licensePlate)
      },
      enableSorting: true,
      enableHiding: false,
      enableResizing: true,
    },
    {
      accessorKey: 'make',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.make') || 'Make',
        }),
      cell: ({ row }) => {
        const make = row.getValue('make')
        return h(
          'div',
          {},
          (typeof make === 'string' ? make : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'model',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.model') || 'Model',
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
      accessorKey: 'year',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.year') || 'Year',
        }),
      cell: ({ row }) => {
        const year = row.getValue('year')
        return h(
          'div',
          {},
          (typeof year === 'number' ? year.toString() : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'color',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.color') || 'Color',
        }),
      cell: ({ row }) => {
        const color = row.getValue('color')
        return h(
          'div',
          {},
          (typeof color === 'string' ? color : null) || '—',
        )
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.createdAt') || 'Created At',
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
 * Factory function to create vehicle table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getVehicleColumns(): ColumnDef<IVehicle>[] {
  return createColumns()
}
