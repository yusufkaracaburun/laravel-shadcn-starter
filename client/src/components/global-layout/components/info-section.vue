<script setup lang="ts">
import { computed } from 'vue'

import { cn } from '@/lib/utils'

interface InfoSectionProps {
  title: string
  withBorder?: boolean
  titleSpacing?: 'sm' | 'md' | 'lg'
  contentSpacing?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<InfoSectionProps>(), {
  withBorder: true,
  titleSpacing: 'sm',
  contentSpacing: 'md',
})

const titleSpacingClass = computed(() => {
  switch (props.titleSpacing) {
    case 'sm':
      return 'space-y-3'
    case 'lg':
      return 'space-y-5'
    case 'md':
    default:
      return 'space-y-4'
  }
})

const contentSpacingClass = computed(() => {
  switch (props.contentSpacing) {
    case 'sm':
      return 'space-y-2'
    case 'lg':
      return 'space-y-4'
    case 'md':
    default:
      return 'space-y-3'
  }
})

const containerClass = computed(() => {
  return cn(
    titleSpacingClass.value,
    props.withBorder && 'py-3 border-t border-border',
  )
})
</script>

<template>
  <div :class="containerClass">
    <h4 class="font-semibold text-sm">
      {{ props.title }}
    </h4>
    <div :class="contentSpacingClass">
      <slot />
    </div>
  </div>
</template>
