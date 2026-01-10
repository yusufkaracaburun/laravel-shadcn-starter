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
} from '@/composables/use-icons.composable'
import {
  getInvoiceStatusColor,
  getPaymentStatusColor,
} from '@/utils/status-colors'

import type { TInvoiceStatus, TPaymentStatus } from '../models/invoice'

import { EInvoiceStatus, EPaymentStatus } from '../models/invoice'

export interface IStatusItem<T> {
  id: number
  value: T
  label: string
  description: string
  icon: any
  getColor: () => string
}

export const INVOICE_STATUSES: IStatusItem<TInvoiceStatus>[] = [
  {
    id: 1,
    value: EInvoiceStatus.DRAFT,
    label: 'Draft',
    description: 'The invoice is a draft',
    icon: h(CircleDotIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: EInvoiceStatus.SENT,
    label: 'Sent',
    description: 'The invoice has been sent to the customer',
    icon: h(SendIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: EInvoiceStatus.PAID,
    label: 'Paid',
    description: 'The invoice has been paid',
    icon: h(FileCheckIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: EInvoiceStatus.UNPAID,
    label: 'Unpaid',
    description: 'The invoice has not been paid',
    icon: h(CircleIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: EInvoiceStatus.PARTIAL_PAID,
    label: 'Partial Paid',
    description: 'The invoice has been partially paid',
    icon: h(CreditCardIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 6,
    value: EInvoiceStatus.OVERDUE,
    label: 'Overdue',
    description: 'The invoice is overdue',
    icon: h(XCircleIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 7,
    value: EInvoiceStatus.REMINDER,
    label: 'Reminder',
    description: 'A reminder has been sent for this invoice',
    icon: h(ClockIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 8,
    value: EInvoiceStatus.CANCELLED,
    label: 'Cancelled',
    description: 'The invoice has been cancelled',
    icon: h(FileXIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 9,
    value: EInvoiceStatus.REFUNDED,
    label: 'Refunded',
    description: 'The invoice has been refunded',
    icon: h(CircleCheckIcon),
    getColor() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    id: 10,
    value: EInvoiceStatus.CREDITED,
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
    value: EPaymentStatus.PENDING,
    label: 'Pending',
    description: 'The payment is pending',
    icon: h(CircleDotIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 2,
    value: EPaymentStatus.PAID,
    label: 'Paid',
    description: 'The payment has been paid',
    icon: h(FileCheckIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 3,
    value: EPaymentStatus.FAILED,
    label: 'Failed',
    description: 'The payment has failed',
    icon: h(XCircleIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 4,
    value: EPaymentStatus.REFUNDED,
    label: 'Refunded',
    description: 'The payment has been refunded',
    icon: h(CircleCheckIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
  {
    id: 5,
    value: EPaymentStatus.CANCELLED,
    label: 'Cancelled',
    description: 'The payment has been cancelled',
    icon: h(FileXIcon),
    getColor() {
      return getPaymentStatusColor(this.value)
    },
  },
] as IStatusItem<TPaymentStatus>[]
