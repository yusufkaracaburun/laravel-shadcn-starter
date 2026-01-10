import { h } from 'vue'

import type { IFacetedFilterOption } from '@/components/data-table/types'

import {
  AwardIcon,
  BadgeDollarSignIcon,
  HandshakeIcon,
  ShieldIcon,
  UserIcon,
} from '@/composables/use-icons.composable'

import { EUserRole } from '../models/users'

export const callTypes: (IFacetedFilterOption & { style: string })[] = [
  {
    label: 'Active',
    value: 'active',
    style: 'bg-teal-100/30 text-teal-900 dark:text-teal-200 border-teal-200',
  },
  {
    label: 'Inactive',
    value: 'inactive',
    style: 'bg-neutral-300/40 border-neutral-300',
  },
  {
    label: 'Invited',
    value: 'invited',
    style: 'bg-sky-200/40 text-sky-900 dark:text-sky-100 border-sky-300',
  },
  {
    label: 'Suspended',
    value: 'suspended',
    style:
      'bg-destructive/10 dark:bg-destructive/50 text-destructive dark:text-primary border-destructive/10',
  },
]

export const userTypes: IFacetedFilterOption[] = [
  {
    label: 'Super Admin',
    value: EUserRole.SUPER_ADMIN,
    icon: h(BadgeDollarSignIcon),
  },
  {
    label: 'Admin',
    value: EUserRole.ADMIN,
    icon: h(HandshakeIcon),
  },
  {
    label: 'User',
    value: EUserRole.USER,
    icon: h(UserIcon),
  },
  {
    label: 'Customer',
    value: EUserRole.CUSTOMER,
    icon: h(AwardIcon),
  },
  {
    label: 'Contractor',
    value: EUserRole.CONTRACTOR,
    icon: h(ShieldIcon),
  },
] as const
