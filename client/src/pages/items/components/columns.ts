import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'

import type { Item } from '../data/schema'

import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<Item>[] = [
  SelectColumn as ColumnDef<Item>,
  {
    accessorKey: 'id',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Item ID' }),
    cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const item = row.original
      const router = useRouter()
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '') || ''

      return h(
        'button',
        {
          class:
            'max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({
              name: '/items/[id]',
              params: { id: item.id.toString() },
            })
          },
        },
        name,
      )
    },
    enableSorting: true,
    enableResizing: true,
    enableHiding: false,
  },
  {
    accessorKey: 'description',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Description' }),
    cell: ({ row }) => {
      const description = row.getValue('description') as string | null
      return h(
        'div',
        { class: 'max-w-[300px] truncate text-muted-foreground' },
        description || '-',
      )
    },
    enableSorting: false,
  },
  {
    accessorKey: 'unit_price',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Unit Price' }),
    cell: ({ row }) => {
      const item = row.original
      const priceValue = item.unit_price
      // Handle Money object from backend
      if (
        priceValue
        && typeof priceValue === 'object'
        && 'formatted' in priceValue
      ) {
        const money = priceValue as { formatted: string }
        return h('div', { class: 'w-[100px]' }, money.formatted)
      }
      // Fallback for number (backward compatibility)
      if (typeof priceValue === 'number') {
        return h(
          'div',
          { class: 'w-[100px]' },
          new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
          }).format(priceValue),
        )
      }
      return h('div', { class: 'w-[100px] text-muted-foreground' }, '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'vat_rate',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'VAT Rate' }),
    cell: ({ row }) => {
      const vatRate = row.getValue('vat_rate') as number
      return h('div', { class: 'w-[100px]' }, `${vatRate}%`)
    },
    enableSorting: true,
  },
  {
    accessorKey: 'unit',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Unit' }),
    cell: ({ row }) => {
      const unit = row.getValue('unit') as string | null
      return h('div', { class: 'w-[80px]' }, unit || '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'created_at',
    header: ({ column }) =>
      h(DataTableColumnHeader<Item>, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('created_at') as string | null | undefined
      if (!dateValue) {
        return h('div', { class: 'w-[100px] text-muted-foreground' }, '-')
      }
      const date = new Date(dateValue)
      return h(
        'div',
        { class: 'w-[100px]' },
        date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        }),
      )
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
