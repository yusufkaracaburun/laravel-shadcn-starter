import { CheckCircle2, XCircle, Wrench } from 'lucide-vue-next'
import { h } from 'vue'

import { getEquipmentStatusColor } from '@/utils/status-colors'

export const statuses = [
  {
    value: 'active',
    label: 'Active',
    icon: h(CheckCircle2),
    get color() {
      return getEquipmentStatusColor(this.value)
    },
  },
  {
    value: 'inactive',
    label: 'Inactive',
    icon: h(XCircle),
    get color() {
      return getEquipmentStatusColor(this.value)
    },
  },
  {
    value: 'maintenance',
    label: 'Maintenance',
    icon: h(Wrench),
    get color() {
      return getEquipmentStatusColor(this.value)
    },
  },
]
