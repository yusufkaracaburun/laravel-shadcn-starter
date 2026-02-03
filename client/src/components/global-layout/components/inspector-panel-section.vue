<script lang="ts" setup>
import { computed } from 'vue'

import { cn } from '@/lib/utils'

interface InspectorPanelSectionProps {
  title: string
  withBorder?: boolean
  titleSpacing?: 'sm' | 'md' | 'lg'
  contentSpacing?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<InspectorPanelSectionProps>(), {
  withBorder: false,
  titleSpacing: 'md',
  contentSpacing: 'md',
})

const slots = defineSlots<{
  default?: () => any
}>()

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
    props.withBorder && 'pt-6 border-t border-border',
  )
})
</script>

<template>
  <div :class="containerClass">
    <h4 class="font-semibold text-sm">
      {{ title }}
    </h4>
    <div :class="contentSpacingClass">
      <slot />
    </div>
  </div>
</template>
