import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'

import type { IProduct } from '../models/products'

import DataTableRowActions from './data-table-row-actions.vue'
import ProductNameCell from './product-name-cell.vue'

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
function createPriceCell(product: IProduct) {
  const priceValue = product.unit_price
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
 * This is the single source of truth for product columns (DRY principle)
 *
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IProduct>[] {
  const t = getT()

  return [
    SelectColumn as ColumnDef<IProduct>,
    {
      accessorKey: 'id',
      header: ({ column }) =>
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.id'),
        }),
      cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
      enableSorting: false,
      enableHiding: false,
    },
    {
      accessorKey: 'name',
      header: ({ column }) =>
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.name'),
        }),
      cell: ({ row }) => {
        const product = row.original
        const nameValue = row.getValue('name')
        const name = (typeof nameValue === 'string' ? nameValue : '—') || '—'

        return h(ProductNameCell, { product, name })
      },
      enableSorting: true,
      enableResizing: true,
      enableHiding: false,
    },
    {
      accessorKey: 'description',
      header: ({ column }) =>
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.description'),
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
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.unitPrice'),
        }),
      cell: ({ row }) => {
        const product = row.original
        return createPriceCell(product)
      },
      enableSorting: true,
      enableResizing: true,
    },
    {
      accessorKey: 'vat_rate',
      header: ({ column }) =>
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.vatRate'),
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
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.unit'),
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
        h(DataTableColumnHeader<IProduct>, {
          column,
          title: t('products.columns.createdAt'),
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
 * Factory function to create product table columns
 * This pattern ensures proper structure and avoids calling
 * composables inside render functions
 *
 * @returns Array of column definitions
 */
export function getProductColumns(): ColumnDef<IProduct>[] {
  return createColumns()
}
