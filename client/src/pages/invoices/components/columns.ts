import type { ColumnDef } from '@tanstack/vue-table'
import type { Router } from 'vue-router'

import { h } from 'vue'
import { useRouter } from 'vue-router'

import type { Customer } from '@/services/customers.service'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { StatusBadge } from '@/components/ui/status-badge'
import { FileTextIcon } from '@/composables/use-icons'
import { getT } from '@/plugins/i18n/setup'
import { formatDate } from '@/utils/date'
import { formatMoney } from '@/utils/money'

import type { IStatusItem } from '../data/data'
import type { IInvoice } from '../models/invoice'

import { INVOICE_STATUSES } from '../data/data'
import DataTableRowActions from './data-table-row-actions.vue'

// Route name constants
const ROUTES = {
  INVOICE_VIEW: '/invoices/view/[id]',
  CUSTOMER_VIEW: '/customers/[id]',
} as const

// CSS class constants
const CELL_CLASSES = {
  LINK_BUTTON:
    'font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline',
  INVOICE_NUMBER: 'w-32',
  CUSTOMER_CONTAINER: 'flex items-center gap-2 max-w-[200px] truncate',
  CUSTOMER_ICON_WRAPPER:
    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted',
  DATE_CELL: 'w-[100px]',
  TOTAL_CELL: 'w-[100px] font-medium',
  CREATED_AT_CELL: 'w-[100px] text-muted-foreground',
  EMPTY_STATE: 'w-[150px] text-muted-foreground',
} as const

/**
 * Helper function to format invoice number display
 */
function formatInvoiceNumber(
  invoiceNumber: string | null,
  invoiceId: number,
): string {
  return invoiceNumber || `Invoice #${invoiceId}`
}

/**
 * Helper function to get customer display name
 */
function getCustomerDisplayName(
  customer: Customer | undefined,
  customerId: number,
): string {
  return customer?.name || `Customer #${customerId}`
}

/**
 * Helper function to create invoice number cell
 */
function createInvoiceNumberCell(invoice: IInvoice, router: Router) {
  const displayNumber = formatInvoiceNumber(invoice.invoice_number, invoice.id)

  return h(
    'button',
    {
      class: `${CELL_CLASSES.LINK_BUTTON} ${CELL_CLASSES.INVOICE_NUMBER}`,
      onClick: () => {
        router.push({
          name: ROUTES.INVOICE_VIEW,
          params: { id: invoice.id.toString() },
        })
      },
    },
    displayNumber,
  )
}

/**
 * Helper function to create customer cell
 */
function createCustomerCell(invoice: IInvoice, router: Router) {
  const customer = invoice.customer

  if (!customer) {
    return h('div', { class: CELL_CLASSES.EMPTY_STATE }, '-')
  }

  const customerName = getCustomerDisplayName(customer, invoice.customer_id)

  return h(
    'button',
    {
      class: `${CELL_CLASSES.LINK_BUTTON} ${CELL_CLASSES.CUSTOMER_CONTAINER}`,
      onClick: () => {
        router.push({
          name: ROUTES.CUSTOMER_VIEW,
          params: { id: invoice.customer_id.toString() },
        })
      },
    },
    [
      h('div', { class: CELL_CLASSES.CUSTOMER_ICON_WRAPPER }, [
        h(FileTextIcon, { class: 'h-4 w-4 text-muted-foreground' }),
      ]),
      h('span', { class: 'truncate' }, customerName),
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
    formatDate(dateValueString ?? ''),
  )
}

/**
 * Helper function to create status cell
 */
function createStatusCell(statusValue: unknown) {
  const status = INVOICE_STATUSES.find(
    (s: IStatusItem) => s.value === statusValue,
  )
  if (!status) {
    return null
  }

  return h(StatusBadge, {
    id: status.id,
    status: status.value,
    label: status.label,
    description: status.description,
    icon: status.icon,
    type: 'invoice',
    color: status.getColor(),
  })
}

/**
 * Helper function to create money cell
 */
function createMoneyCell(value: unknown) {
  return h('div', { class: CELL_CLASSES.TOTAL_CELL }, formatMoney(value))
}

/**
 * Creates the column definitions array
 * This is the single source of truth for invoice columns (DRY principle)
 *
 * @param t - Translation function from i18n
 * @param getRouter - Function that returns Router instance (allows lazy evaluation)
 * @returns Array of column definitions
 */
function createColumns(): ColumnDef<IInvoice>[] {
  const t = getT()
  const getRouter = () => useRouter()

  return [
    SelectColumn as ColumnDef<IInvoice>,
    {
      accessorKey: 'invoice_number',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.invoiceNumber'),
        }),
      cell: ({ row }) => createInvoiceNumberCell(row.original, getRouter()),
      enableSorting: true,
      enableHiding: false,
    },
    {
      accessorKey: 'customer',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.customer'),
        }),
      cell: ({ row }) => createCustomerCell(row.original, getRouter()),
      enableSorting: false,
    },
    {
      accessorKey: 'date',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.date'),
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('date')
        return createDateCell(dateValue)
      },
      enableSorting: true,
    },
    {
      accessorKey: 'date_due',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.dueDate'),
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('date_due')
        return createDateCell(dateValue)
      },
      enableSorting: true,
    },
    {
      accessorKey: 'status',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.status'),
        }),
      cell: ({ row }) => {
        const statusValue = row.getValue('status')
        return createStatusCell(statusValue)
      },
      enableSorting: true,
    },
    {
      accessorKey: 'total',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.total'),
        }),
      cell: ({ row }) => {
        const total = row.getValue('total')
        return createMoneyCell(total)
      },
      enableSorting: true,
    },
    {
      accessorKey: 'created_at',
      header: ({ column }) =>
        h(DataTableColumnHeader<IInvoice>, {
          column,
          title: t('invoices.columns.createdAt'),
        }),
      cell: ({ row }) => {
        const dateValue = row.getValue('created_at')
        return h(
          'div',
          { class: CELL_CLASSES.CREATED_AT_CELL },
          formatDate(dateValue),
        )
      },
      enableSorting: true,
    },
    {
      id: 'actions',
      cell: ({ row }) => h(DataTableRowActions, { row }),
    },
  ]
}

/**
 * Factory function to create invoice table columns with router instance
 * This pattern ensures router is properly injected and avoids calling
 * composables inside render functions
 *
 * @param router - Vue Router instance for navigation
 * @returns Array of column definitions
 */
export function getInvoiceColumns(): ColumnDef<IInvoice>[] {
  return createColumns()
}
