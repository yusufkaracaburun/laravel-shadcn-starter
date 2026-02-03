import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import Badge from '@/components/ui/badge/Badge.vue'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IVehicle } from '../models/vehicles'

import { statuses } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

// CSS class constants
const CELL_CLASSES = {
  LICENSE_PLATE_CONTAINER: 'flex items-center gap-2',
  DATE_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create license plate cell with copy functionality and navigation
 */
function createLicensePlateCell(
  licensePlateValue: unknown,
  vehicle: IVehicle,
) {
  const licensePlateStr =
    licensePlateValue && typeof licensePlateValue === 'string' ?
      licensePlateValue :
      ''
  const router = useRouter()

  return h(
    'button',
    {
      class:
        'flex items-center gap-2 max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
      onClick: () => {
        router.push({
          name: '/vehicles/view/[id]',
          params: { id: vehicle.id.toString() },
        })
      },
    },
    [
      h('span', { class: 'truncate' }, licensePlateStr || '—'),
      ...(licensePlateStr ?
          [
            h(Copy, {
              class: 'ml-2 flex-shrink-0',
              size: 'sm',
              variant: 'ghost',
              content: licensePlateStr,
              onClick: (e: Event) => {
                e.stopPropagation()
              },
            }),
          ] :
          []),
    ],
  )
}

/**
 * Helper function to create date cell
 */
function createDateCell(dateValue: unknown) {
  const dateValueString = dateValue as string | null | undefined
  return h(
    'div',
    { class: CELL_CLASSES.DATE_CELL },
    dateValueString ? formatDate(dateValueString) : '—',
  )
}

/**
 * Helper function to get status info from statuses data
 */
function getStatusInfo(status: string | null | undefined) {
  if (!status) {
    return null
  }

  return statuses.find((statusItem) => {
    return statusItem.value.toLowerCase() === status.toLowerCase()
  })
}

/**
 * Helper function to get status variant for Badge
 */
function getStatusVariant(status: string | null | undefined) {
  const statusInfo = getStatusInfo(status)
  if (!statusInfo) {
    return 'secondary'
  }

  switch (statusInfo.value) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'destructive'
    case 'maintenance':
      return 'secondary'
    default:
      return 'secondary'
  }
}

/**
 * Helper function to create status badge cell
 */
function createStatusCell(vehicle: IVehicle) {
  if (!vehicle.status) {
    return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '—')
  }

  const statusInfo = getStatusInfo(vehicle.status)
  const variant = getStatusVariant(vehicle.status)

  return h(
    Badge,
    {
      variant,
      class: statusInfo?.color || '',
    },
    [statusInfo?.label || vehicle.status],
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
        const vehicle = row.original
        const licensePlate = row.getValue('license_plate')
        return createLicensePlateCell(licensePlate, vehicle)
      },
      enableSorting: true,
      enableHiding: false,
      enableResizing: true,
    },
    {
      accessorKey: 'status',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.status') || 'Status',
        }),
      cell: ({ row }) => {
        const vehicle = row.original
        return createStatusCell(vehicle)
      },
      filterFn: (row, id, value) => {
        return value.includes(row.getValue(id))
      },
      enableSorting: true,
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
        return h('div', {}, (typeof make === 'string' ? make : null) || '—')
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
        return h('div', {}, (typeof model === 'string' ? model : null) || '—')
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
      accessorKey: 'inspection_date',
      header: ({ column }) =>
        h(DataTableColumnHeader<IVehicle>, {
          column,
          title: t('vehicles.columns.inspectionDate') || 'Inspection Date',
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('inspection_date')
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
