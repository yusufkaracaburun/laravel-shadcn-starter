import type { ColumnDef } from '@tanstack/vue-table'

import { Building2Icon, UserIcon } from '@/composables/use-icons.composable'
import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import { StatusBadge } from '@/components/ui/status-badge'

import type { ICustomer } from '../models/customers'

import { statuses } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<ICustomer>[] = [
  SelectColumn as ColumnDef<ICustomer>,
  {
    accessorKey: 'name',
    header: ({ column }) =>
      h(DataTableColumnHeader<ICustomer>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const customer = row.original
      const router = useRouter()
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '') || ''
      const type = customer.type as string
      const isBusiness = type === 'business'
      const typeIcon = isBusiness ? Building2Icon : UserIcon

      return h(
        'button',
        {
          class:
            'flex items-center gap-2 max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({
              name: '/customers/view/[id]',
              params: { id: customer.id.toString() },
            })
          },
        },
        [
          h(
            'div',
            {
              class:
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted',
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
      h(DataTableColumnHeader<ICustomer>, { column, title: 'Primary Contact' }),
    cell: ({ row }) => {
      const customer = row.original
      const primaryContact = customer.primary_contact

      if (!primaryContact) {
        return h('div', { class: 'w-[150px] text-muted-foreground' }, '-')
      }

      return h(
        'div',
        { class: 'w-[150px]' },
        primaryContact.name || primaryContact.email || '-',
      )
    },
    enableSorting: false,
  },
  {
    accessorKey: 'email',
    header: ({ column }) =>
      h(DataTableColumnHeader<ICustomer>, { column, title: 'Email' }),
    cell: ({ row }) => {
      const email = row.getValue('email') as string | null
      const emailStr = email || ''
      return h('div', { class: 'flex items-center max-w-[200px]' }, [
        h('span', { class: 'truncate text-muted-foreground' }, emailStr || '-'),
        emailStr
        && h(Copy, {
          class: 'ml-2 flex-shrink-0',
          size: 'sm',
          variant: 'ghost',
          content: emailStr,
        }),
      ])
    },
    enableSorting: true,
  },
  {
    accessorKey: 'phone',
    header: ({ column }) =>
      h(DataTableColumnHeader<ICustomer>, { column, title: 'Phone' }),
    cell: ({ row }) => {
      const phone = row.getValue('phone') as string | null
      return h('div', { class: 'w-[120px]' }, phone || '-')
    },
    enableSorting: true,
  },
  {
    accessorKey: 'status',
    header: ({ column }) =>
      h(DataTableColumnHeader<ICustomer>, { column, title: 'Status' }),
    cell: ({ row }) => {
      const status = statuses.find(
        status => status.value === row.getValue('status'),
      )

      if (!status)
        return null

      return h(StatusBadge, {
        status: status.value,
        type: 'customer',
        icon: status.icon,
        label: status.label,
      })
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
