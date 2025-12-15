import type { ColumnDef } from '@tanstack/vue-table'

import { FileText } from 'lucide-vue-next'
import { h } from 'vue'
import { useRouter } from 'vue-router'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { StatusBadge } from '@/components/ui/status-badge'

import type { Invoice } from '../data/schema'
import { statuses } from '../data/data'

import DataTableRowActions from './data-table-row-actions.vue'

/**
 * Format money value (Money object or number)
 */
function formatMoney(value: any): string {
  if (typeof value === 'object' && value !== null && 'formatted' in value) {
    return value.formatted
  }
  if (typeof value === 'number') {
    return new Intl.NumberFormat('nl-NL', {
      style: 'currency',
      currency: 'EUR',
    }).format(value)
  }
  return '€0.00'
}

/**
 * Format date from "d-m-Y H:i:s" or "Y-m-d" format
 */
function formatDate(dateString: string | null | undefined): string {
  if (!dateString) {
    return '-'
  }
  // Try parsing as "d-m-Y H:i:s" format first
  if (dateString.includes('-') && dateString.includes(' ')) {
    const [datePart, timePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('-')
    if (day && month && year && year.length === 4) {
      const date = new Date(`${year}-${month}-${day} ${timePart}`)
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    }
  }
  // Try parsing as "Y-m-d" format
  const date = new Date(dateString)
  if (!isNaN(date.getTime())) {
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
  }
  return dateString
}

export const columns: ColumnDef<Invoice>[] = [
  SelectColumn as ColumnDef<Invoice>,
  {
    accessorKey: 'invoice_number',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Invoice Number' }),
    cell: ({ row }) => {
      const invoice = row.original
      const router = useRouter()
      const invoiceNumber = row.getValue('invoice_number') as string | null
      const displayNumber = invoiceNumber || `Invoice #${invoice.id}`

      return h(
        'button',
        {
          class:
            'w-32 font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({ name: '/invoices/[id]', params: { id: invoice.id.toString() } })
          },
        },
        displayNumber,
      )
    },
    enableSorting: true,
    enableHiding: false,
  },
  {
    accessorKey: 'customer',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Customer' }),
    cell: ({ row }) => {
      const invoice = row.original
      const router = useRouter()
      const customer = invoice.customer

      if (!customer) {
        return h('div', { class: 'w-[150px] text-muted-foreground' }, '-')
      }

      const customerName = (customer as any).name || `Customer #${invoice.customer_id}`

      return h(
        'button',
        {
          class:
            'flex items-center gap-2 max-w-[200px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
          onClick: () => {
            router.push({ name: '/customers/[id]', params: { id: invoice.customer_id.toString() } })
          },
        },
        [
          h(
            'div',
            {
              class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted',
            },
            [h(FileText, { class: 'h-4 w-4 text-muted-foreground' })],
          ),
          h('span', { class: 'truncate' }, customerName),
        ],
      )
    },
    enableSorting: false,
  },
  {
    accessorKey: 'date',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Date' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('date') as string | null | undefined
      return h('div', { class: 'w-[100px]' }, formatDate(dateValue))
    },
    enableSorting: true,
  },
  {
    accessorKey: 'date_due',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Due Date' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('date_due') as string | null | undefined
      return h('div', { class: 'w-[100px]' }, formatDate(dateValue))
    },
    enableSorting: true,
  },
  {
    accessorKey: 'status',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Status' }),
    cell: ({ row }) => {
      const statusValue = row.getValue('status') as string
      const status = statuses.find((s) => s.value === statusValue)
      if (!status) return null
      return h(StatusBadge, {
        status: status.value,
        type: 'invoice',
        icon: status.icon,
        label: status.label,
      })
    },
    enableSorting: true,
  },
  {
    accessorKey: 'total',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Total' }),
    cell: ({ row }) => {
      const total = row.getValue('total')
      return h('div', { class: 'w-[100px] font-medium' }, formatMoney(total))
    },
    enableSorting: true,
  },
  {
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader<Invoice>, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const dateValue = row.getValue('created_at') as string | null | undefined
      return h('div', { class: 'w-[100px] text-muted-foreground' }, formatDate(dateValue))
    },
    enableSorting: true,
  },
  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
