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

import { getPriorityColor, getTaskStatusColor } from '@/utils/status-colors'

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
    get color() {
      return getTaskStatusColor(this.value)
    },
  },
  {
    value: 'todo',
    label: 'Todo',
    icon: h(Circle),
    get color() {
      return getTaskStatusColor(this.value)
    },
  },
  {
    value: 'in progress',
    label: 'In Progress',
    icon: h(TimerOff),
    get color() {
      return getTaskStatusColor(this.value)
    },
  },
  {
    value: 'done',
    label: 'Done',
    icon: h(CircleCheck),
    get color() {
      return getTaskStatusColor(this.value)
    },
  },
  {
    value: 'canceled',
    label: 'Canceled',
    icon: h(CirclePlus),
    get color() {
      return getTaskStatusColor(this.value)
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
