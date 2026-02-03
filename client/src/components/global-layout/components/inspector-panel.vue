<script lang="ts" setup>
import { PanelRightCloseIcon } from '@/composables/use-icons.composable'

interface InspectorPanelProps {
  title: string
  subtitle?: string
}

defineProps<InspectorPanelProps>()

const emit = defineEmits<{
  close: []
}>()

const slots = defineSlots<{
  default?: () => any
  header?: () => any
}>()
</script>

<template>
  <div class="w-80 border-l border-border bg-background flex flex-col h-screen">
    <div class="flex items-center justify-between p-4 border-b shrink-0">
      <div v-if="slots.header" class="flex-1">
        <slot name="header" />
      </div>
      <h4 v-else class="text-base font-semibold">
        <span v-if="subtitle" class="text-muted-foreground">{{ subtitle }}</span>
        {{ title }}
      </h4>
      <button
        class="text-muted-foreground hover:text-foreground transition-colors"
        @click="emit('close')"
      >
        <PanelRightCloseIcon class="size-5" />
        <span class="sr-only">Close sidebar</span>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
      <slot />
    </div>
  </div>
</template>
