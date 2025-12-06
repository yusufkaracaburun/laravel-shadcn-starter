import {
  ArrowDown,
  ArrowRight,
  ArrowUp,
  CheckCircle2,
  Pause,
  XCircle,
} from 'lucide-vue-next'
import { h } from 'vue'

import { getPriorityColor, getProjectStatusColor } from '@/utils/status-colors'

export const categories = [
  {
    value: 'development',
    label: 'Development',
  },
  {
    value: 'design',
    label: 'Design',
  },
  {
    value: 'marketing',
    label: 'Marketing',
  },
  {
    value: 'support',
    label: 'Support',
  },
]

export const statuses = [
  {
    value: 'active',
    label: 'Active',
    icon: h(CheckCircle2),
    get color() {
      return getProjectStatusColor(this.value)
    },
  },
  {
    value: 'completed',
    label: 'Completed',
    icon: h(CheckCircle2),
    get color() {
      return getProjectStatusColor(this.value)
    },
  },
  {
    value: 'on-hold',
    label: 'On Hold',
    icon: h(Pause),
    get color() {
      return getProjectStatusColor(this.value)
    },
  },
  {
    value: 'cancelled',
    label: 'Cancelled',
    icon: h(XCircle),
    get color() {
      return getProjectStatusColor(this.value)
    },
  },
]

export const priorities = [
  {
    value: 'low',
    label: 'Low',
    icon: h(ArrowDown),
    get color() {
      return getPriorityColor(this.value)
    },
  },
  {
    value: 'medium',
    label: 'Medium',
    icon: h(ArrowRight),
    get color() {
      return getPriorityColor(this.value)
    },
  },
  {
    value: 'high',
    label: 'High',
    icon: h(ArrowUp),
    get color() {
      return getPriorityColor(this.value)
    },
  },
]

