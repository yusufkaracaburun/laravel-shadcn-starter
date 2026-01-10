import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IItem } from '../models/items'

import DataTableRowActions from './data-table-row-actions.vue'
import ItemNameCell from './item-name-cell.vue'

// CSS class constants
const CELL_CLASSES = {
  DESCRIPTION_CELL: 'max-w-[300px] truncate text-muted-foreground',
  PRICE_CELL: 'w-[100px]',
  VAT_RATE_CELL: 'w-[100px]',
  UNIT_CELL: 'w-[80px]',
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'text-muted-foreground',
} as const

/**
 * Helper function to create price cell
 */
function createPriceCell(item: IItem) {
  const priceValue = item.unit_price
  // Handle Money object from backend
  if (
    priceValue
    && typeof priceValue === 'object'
    && 'formatted' in priceValue
  ) {
    const money = priceValue as { formatted: string }
    return h('div', { class: CELL_CLASSES.PRICE_CELL }, money.formatted)
  }
  // Fallback for number (backward compatibility)
  if (typeof priceValue === 'number') {
    return h(
      'div',
      { class: CELL_CLASSES.PRICE_CELL },
      new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
      }).format(priceValue),
    )
  }
  return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '—')
}

/**
 * Helper function to create description cell
 */
function createDescriptionCell(description: string | null | undefined) {
  return h(
    'div',
    { class: CELL_CLASSES.DESCRIPTION_CELL },
    description || '—',
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
 * This is the single source of truth for item columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IItem>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<IItem>,
    {
      accessorKey: 'id',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.id'),
        }),
      cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
      enableSorting: false,
      enableHiding: false,
    },
    {
      accessorKey: 'name',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.name'),
        }),
      cell: ({ row }) => {
        const item = row.original
        const nameValue = row.getValue('name')
        const name = (typeof nameValue === 'string' ? nameValue : '—') || '—'

        return h(ItemNameCell, { item, name })
      },
      enableSorting: true,
      enableResizing: true,
      enableHiding: false,
    },
    {
      accessorKey: 'description',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.description'),
        }),
      cell: ({ row }) => {
        const description = row.getValue('description') as string | null
        return createDescriptionCell(description)
      },
      enableSorting: false,
      enableResizing: true,
    },
    {
      accessorKey: 'unit_price',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.unitPrice'),
        }),
      cell: ({ row }) => {
        const item = row.original
        return createPriceCell(item)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'vat_rate',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.vatRate'),
        }),
      cell: ({ row }) => {
        const vatRate = row.getValue('vat_rate') as number
        return h('div', { class: CELL_CLASSES.VAT_RATE_CELL }, `${vatRate}%`)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'unit',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.unit'),
        }),
      cell: ({ row }) => {
        const unit = row.getValue('unit') as string | null
        return h('div', { class: CELL_CLASSES.UNIT_CELL }, unit || '—')
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<IItem>, {
          column,
          title: t('items.columns.createdAt'),
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
 * Factory function to create item table columns
 * This pattern ensures proper structure and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getItemColumns(): ColumnDef<IItem>[] {
  return createColumns()
}
