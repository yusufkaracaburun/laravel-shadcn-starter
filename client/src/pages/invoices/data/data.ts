import { h } from 'vue'

import {
  CircleCheckIcon,
  CircleDotIcon,
  FileCheckIcon,
  FileXIcon,
  SendIcon,
  XCircleIcon,
} from '@/composables/use-icons'
import {
  getInvoiceStatusColor,
  getPaymentStatusColor,
} from '@/utils/status-colors'

export interface IStatusItem {
  id: number
  value: string
  label: string
  description: string
  icon: any
  getColor: () => string
}

export const INVOICE_STATUSES = [
  {
    id: 1,
    value: 'pending',
    label: 'Pending',
    description: 'The invoice is pending payment',
    icon: h(CircleDotIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: 'draft',
    label: 'Draft',
    description: 'The invoice is a draft',
    icon: h(CircleDotIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: 'sent',
    label: 'Sent',
    description: 'The invoice has been sent to the customer',
    icon: h(SendIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: 'overdue',
    label: 'Overdue',
    description: 'The invoice is overdue',
    icon: h(XCircleIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: 'cancelled',
    label: 'Cancelled',
    description: 'The invoice has been cancelled',
    icon: h(FileXIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 6,
    value: 'paid',
    label: 'Paid',
    description: 'The invoice has been paid',
    icon: h(FileCheckIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
] as IStatusItem[]

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
