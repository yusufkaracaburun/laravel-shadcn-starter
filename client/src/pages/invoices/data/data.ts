import { CircleDot, FileCheck, FileX, Send, XCircle } from 'lucide-vue-next'
import { h } from 'vue'

import { getInvoiceStatusColor } from '@/utils/status-colors'

export const statuses = [
  {
    value: 'draft',
    label: 'Draft',
    icon: h(CircleDot),
    get color() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    value: 'sent',
    label: 'Sent',
    icon: h(Send),
    get color() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    value: 'paid',
    label: 'Paid',
    icon: h(FileCheck),
    get color() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    value: 'overdue',
    label: 'Overdue',
    icon: h(XCircle),
    get color() {
      return getInvoiceStatusColor(this.value)
    },
  },
  {
    value: 'cancelled',
    label: 'Cancelled',
    icon: h(FileX),
    get color() {
      return getInvoiceStatusColor(this.value)
    },
  },
]
