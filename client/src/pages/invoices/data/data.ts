import { h } from 'vue'

import {
  CircleCheckIcon,
  CircleDotIcon,
  CircleIcon,
  ClockIcon,
  CreditCardIcon,
  FileCheckIcon,
  FileXIcon,
  ReceiptIcon,
  SendIcon,
  XCircleIcon,
} from '@/composables/use-icons'
import {
  getInvoiceStatusColor,
  getPaymentStatusColor,
} from '@/utils/status-colors'

import type { TInvoiceStatus } from '../models/invoice'

export interface IStatusItem {
  id: number
  value: TInvoiceStatus
  label: string
  description: string
  icon: any
  getColor: () => string
}

export const INVOICE_STATUSES: IStatusItem[] = [
  {
    id: 1,
    value: 'draft',
    label: 'Draft',
    description: 'The invoice is a draft',
    icon: h(CircleDotIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: 'sent',
    label: 'Sent',
    description: 'The invoice has been sent to the customer',
    icon: h(SendIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: 'paid',
    label: 'Paid',
    description: 'The invoice has been paid',
    icon: h(FileCheckIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: 'unpaid',
    label: 'Unpaid',
    description: 'The invoice has not been paid',
    icon: h(CircleIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: 'partial_paid',
    label: 'Partial Paid',
    description: 'The invoice has been partially paid',
    icon: h(CreditCardIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 6,
    value: 'overdue',
    label: 'Overdue',
    description: 'The invoice is overdue',
    icon: h(XCircleIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 7,
    value: 'reminder',
    label: 'Reminder',
    description: 'A reminder has been sent for this invoice',
    icon: h(ClockIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 8,
    value: 'cancelled',
    label: 'Cancelled',
    description: 'The invoice has been cancelled',
    icon: h(FileXIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 9,
    value: 'refunded',
    label: 'Refunded',
    description: 'The invoice has been refunded',
    icon: h(CircleCheckIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 10,
    value: 'credited',
    label: 'Credited',
    description: 'The invoice has been credited',
    icon: h(ReceiptIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
]

export const paymentStatuses = [
  {
    id: 1,
    value: 'pending',
    label: 'Pending',
    description: 'The payment is pending',
    icon: h(CircleDotIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: 'paid',
    label: 'Paid',
    description: 'The payment has been paid',
    icon: h(FileCheckIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: 'failed',
    label: 'Failed',
    description: 'The payment has failed',
    icon: h(XCircleIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: 'refunded',
    label: 'Refunded',
    description: 'The payment has been refunded',
    icon: h(CircleCheckIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: 'cancelled',
    label: 'Cancelled',
    description: 'The payment has been cancelled',
    icon: h(FileXIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
] as IStatusItem[]
