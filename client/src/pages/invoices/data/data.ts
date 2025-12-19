import {
  CircleCheck,
  CircleDot,
  FileCheck,
  FileX,
  Send,
  XCircle,
} from 'lucide-vue-next'
import { h } from 'vue'

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

export const statuses = [
  {
    id: 1,
    value: 'pending',
    label: 'Pending',
    description: 'The invoice is pending payment',
    icon: h(CircleDot),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: 'draft',
    label: 'Draft',
    description: 'The invoice is a draft',
    icon: h(CircleDot),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: 'sent',
    label: 'Sent',
    description: 'The invoice has been sent to the customer',
    icon: h(Send),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: 'overdue',
    label: 'Overdue',
    description: 'The invoice is overdue',
    icon: h(XCircle),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: 'cancelled',
    label: 'Cancelled',
    description: 'The invoice has been cancelled',
    icon: h(FileX),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 6,
    value: 'paid',
    label: 'Paid',
    description: 'The invoice has been paid',
    icon: h(FileCheck),
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
    icon: h(CircleDot),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: 'paid',
    label: 'Paid',
    description: 'The payment has been paid',
    icon: h(FileCheck),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: 'failed',
    label: 'Failed',
    description: 'The payment has failed',
    icon: h(XCircle),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: 'refunded',
    label: 'Refunded',
    description: 'The payment has been refunded',
    icon: h(CircleCheck),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: 'cancelled',
    label: 'Cancelled',
    description: 'The payment has been cancelled',
    icon: h(FileX),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
] as IStatusItem[]
