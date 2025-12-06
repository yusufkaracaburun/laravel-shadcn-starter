import {
  ArrowDown,
  ArrowRight,
  ArrowUp,
  Circle,
  CircleCheck,
  CircleHelp,
  CirclePlus,
  TimerOff,
} from 'lucide-vue-next'
import { h } from 'vue'

export const labels = [
  {
    value: 'bug',
    label: 'Bug',
  },
  {
    value: 'feature',
    label: 'Feature',
  },
  {
    value: 'documentation',
    label: 'Documentation',
  },
]

export const statuses = [
  {
    value: 'backlog',
    label: 'Backlog',
    icon: h(CircleHelp),
    color: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
  },
  {
    value: 'todo',
    label: 'Todo',
    icon: h(Circle),
    color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  },
  {
    value: 'in progress',
    label: 'In Progress',
    icon: h(TimerOff),
    color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  },
  {
    value: 'done',
    label: 'Done',
    icon: h(CircleCheck),
    color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  },
  {
    value: 'canceled',
    label: 'Canceled',
    icon: h(CirclePlus),
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
