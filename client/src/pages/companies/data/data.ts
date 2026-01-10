import {
  CheckCircle2Icon,
  ClockIcon,
  XCircleIcon,
} from '@/composables/use-icons.composable'
import { h } from 'vue'

import { getCompanyStatusColor } from '@/utils/status-colors'

export const industries = [
  {
    value: 'technology',
    label: 'Technology',
  },
  {
    value: 'finance',
    label: 'Finance',
  },
  {
    value: 'healthcare',
    label: 'Healthcare',
  },
  {
    value: 'retail',
    label: 'Retail',
  },
  {
    value: 'manufacturing',
    label: 'Manufacturing',
  },
  {
    value: 'education',
    label: 'Education',
  },
]

export const statuses = [
  {
    value: 'active',
    label: 'Active',
    icon: h(CheckCircle2Icon),
    get color() {
      return getCompanyStatusColor(this.value)
    },
  },
  {
    value: 'inactive',
    label: 'Inactive',
    icon: h(XCircleIcon),
    get color() {
      return getCompanyStatusColor(this.value)
    },
  },
  {
    value: 'pending',
    label: 'Pending',
    icon: h(ClockIcon),
    get color() {
      return getCompanyStatusColor(this.value)
    },
  },
]

export const employeeSizes = [
  {
    value: '1-10',
    label: '1-10',
  },
  {
    value: '11-50',
    label: '11-50',
  },
  {
    value: '51-200',
    label: '51-200',
  },
  {
    value: '201-500',
    label: '201-500',
  },
  {
    value: '500+',
    label: '500+',
  },
]
