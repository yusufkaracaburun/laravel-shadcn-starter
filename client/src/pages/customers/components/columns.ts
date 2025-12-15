import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'

import type { Customer } from '../data/schema'

import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<Customer>[] = [
  SelectColumn as ColumnDef<Customer>,
  {
    accessorKey: 'id',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Customer ID' }),
    cell: ({ row }) => h('div', { class: 'w-24' }, row.getValue('id')),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const customer = row.original
      const router = useRouter()
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '') || ''

      return h(
        'button',
        {
          class: 'max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({ name: '/customers/[id]', params: { id: customer.id.toString() } })
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
    accessorKey: 'type',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Type' }),
    cell: ({ row }) => {
      const type = row.getValue('type') as string
      const typeLabel = type === 'business' ? 'Business' : 'Private'
      const typeClass = type === 'business' ? 'badge badge-light-primary' : 'badge badge-light-secondary'

      return h('span', { class: typeClass }, typeLabel)
    },
    enableSorting: true,
  },
  {
    accessorKey: 'email',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Email' }),
    cell: ({ row }) => {
      const email = row.getValue('email') as string | null
      return h('div', { class: 'max-w-[200px] truncate text-muted-foreground' }, email || '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'phone',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Phone' }),
    cell: ({ row }) => {
      const phone = row.getValue('phone') as string | null
      return h('div', { class: 'w-[120px]' }, phone || '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'city',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'City' }),
    cell: ({ row }) => {
      const city = row.getValue('city') as string | null
      return h('div', { class: 'w-[120px]' }, city || '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'primary_contact',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Primary Contact' }),
    cell: ({ row }) => {
      const customer = row.original
      const primaryContact = customer.primary_contact

      if (!primaryContact) {
        return h('div', { class: 'w-[150px] text-muted-foreground' }, '-')
      }

      return h('div', { class: 'w-[150px]' }, primaryContact.name || primaryContact.email || '-')
    },
    enableSorting: false,
  },
  {
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('created_at') as string | null | undefined
      if (!dateValue) {
        return h('div', { class: 'w-[100px] text-muted-foreground' }, '-')
      }
      // Parse date from "d-m-Y H:i:s" format
      const [datePart, timePart] = dateValue.split(' ')
      const [day, month, year] = datePart.split('-')
      const date = new Date(`${year}-${month}-${day} ${timePart}`)
      return h('div', { class: 'w-[100px]' }, date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }))
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]

