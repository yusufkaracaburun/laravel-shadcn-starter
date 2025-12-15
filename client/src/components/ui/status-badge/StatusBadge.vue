<script lang="ts" setup>
import type { Component } from 'vue'
import type { HTMLAttributes } from 'vue'

import { Badge } from '@/components/ui/badge'
import {
  getCompanyStatusColor,
  getInvoiceStatusColor,
  getProjectStatusColor,
  getTaskStatusColor,
} from '@/utils/status-colors'

export type StatusType = 'project' | 'task' | 'invoice' | 'company'

interface Props {
  status: string
  type?: StatusType
  icon?: Component
  label?: string
  class?: HTMLAttributes['class']
  variant?: 'default' | 'secondary' | 'destructive' | 'outline'
}

const props = withDefaults(defineProps<Props>(), {
  type: 'project',
  variant: 'secondary',
})

function getStatusColor(status: string, type: StatusType): string {
  switch (type) {
    case 'project':
      return getProjectStatusColor(status)
    case 'task':
      return getTaskStatusColor(status)
    case 'invoice':
      return getInvoiceStatusColor(status)
    case 'company':
      return getCompanyStatusColor(status)
    default:
      return getProjectStatusColor(status)
  }
}

function getStatusLabel(status: string, customLabel?: string): string {
  if (customLabel) return customLabel
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const statusColor = computed(() => getStatusColor(props.status, props.type))
const statusLabel = computed(() => getStatusLabel(props.status, props.label))
</script>

<template>
  <Badge
    :variant="variant"
    :class="[statusColor, props.class]"
    class="flex w-fit items-center gap-1"
  >
    <component v-if="icon" :is="icon" class="size-3" />
    <span>{{ statusLabel }}</span>
  </Badge>
</template>
