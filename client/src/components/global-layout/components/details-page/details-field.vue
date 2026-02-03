<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue'

import { cn } from '@/lib/utils'

interface Props {
  label: string
  value?: string | number | null | undefined
  icon?: Component
  span?: number
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  value: undefined,
  icon: undefined,
  span: 1,
  class: undefined,
})

const spanClass = computed(() => {
  if (props.span && props.span > 1) {
    return `md:col-span-${props.span}`
  }
  return ''
})
</script>

<template>
  <div
    data-slot="details-field"
    :class="cn(spanClass, props.class)"
  >
    <div class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5">
      <component :is="icon" v-if="icon" class="size-3" />
      {{ label }}
    </div>
    <div class="text-sm">
      <slot>
        {{ value }}
      </slot>
    </div>
  </div>
</template>
