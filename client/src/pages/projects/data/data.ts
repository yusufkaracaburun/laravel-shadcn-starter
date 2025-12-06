import {
  ArrowDown,
  ArrowRight,
  ArrowUp,
  CheckCircle2,
  Pause,
  XCircle,
} from 'lucide-vue-next'
import { h } from 'vue'

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
    color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  },
  {
    value: 'completed',
    label: 'Completed',
    icon: h(CheckCircle2),
    color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  },
  {
    value: 'on-hold',
    label: 'On Hold',
    icon: h(Pause),
    color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  },
  {
    value: 'cancelled',
    label: 'Cancelled',
    icon: h(XCircle),
    color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  },
]

export const priorities = [
  {
    value: 'low',
    label: 'Low',
    icon: h(ArrowDown),
    color: 'text-blue-500',
  },
  {
    value: 'medium',
    label: 'Medium',
    icon: h(ArrowRight),
    color: 'text-warning',
  },
  {
    value: 'high',
    label: 'High',
    icon: h(ArrowUp),
    color: 'text-destructive',
  },
]

