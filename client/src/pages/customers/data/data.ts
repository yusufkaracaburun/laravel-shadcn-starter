import { Circle, CircleCheck, CircleX } from 'lucide-vue-next'
import { h } from 'vue'

import { getCustomerStatusColor } from '@/utils/status-colors'

import { ECustomerStatus } from '../models/customers'

export const statuses = [
  {
    value: ECustomerStatus.REGISTERED,
    label: 'Registered',
    icon: h(Circle),
    get color() {
      return getCustomerStatusColor(this.value)
    },
  },
  {
    value: ECustomerStatus.ACTIVE,
    label: 'Active',
    icon: h(CircleCheck),
    get color() {
      return getCustomerStatusColor(this.value)
    },
  },
  {
    value: ECustomerStatus.INACTIVE,
    label: 'Inactive',
    icon: h(CircleX),
    get color() {
      return getCustomerStatusColor(this.value)
    },
  },
]
