import type { ColumnDef } from '@tanstack/vue-table'

import { Building2, User } from 'lucide-vue-next'
import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'

import type { Customer } from '../data/schema'

import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<Customer>[] = [
  SelectColumn as ColumnDef<Customer>,
  {
    accessorKey: 'number',
    header: ({ column }) =>
      h(DataTableColumnHeader<Customer>, { column, title: 'Customer Number' }),
    cell: ({ row }) => {
      const number = row.getValue('number') as string | number | null | undefined
      return h('div', { class: 'w-24' }, number?.toString() || '-')
    },
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
      const type = customer.type as string
      const isBusiness = type === 'business'
      const typeIcon = isBusiness ? Building2 : User

      return h(
        'button',
        {
          class:
            'flex items-center gap-2 max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({ name: '/customers/[id]', params: { id: customer.id.toString() } })
          },
        },
        [
          h(
            'div',
            {
              class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted',
            },
            [h(typeIcon, { class: 'h-4 w-4 text-muted-foreground' })],
          ),
          h('span', { class: 'truncate' }, name),
        ],
      )
    },
    enableSorting: true,
    enableResizing: true,
    enableHiding: false,
  },
  {
    accessorKey: 'primary_contact',
    header: ({ column }) =>
      h(DataTableColumnHeader<Customer>, { column, title: 'Primary Contact' }),
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
    accessorKey: 'email',
    header: ({ column }) => h(DataTableColumnHeader<Customer>, { column, title: 'Email' }),
    cell: ({ row }) => {
      const email = row.getValue('email') as string | null
      const emailStr = email || ''
      return h('div', { class: 'flex items-center max-w-[200px]' }, [
        h('span', { class: 'truncate text-muted-foreground' }, emailStr || '-'),
        emailStr
        && h(Copy, { class: 'ml-2 flex-shrink-0', size: 'sm', variant: 'ghost', content: emailStr }),
      ])
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
      return h(
        'div',
        { class: 'w-[100px]' },
        date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
      )
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
