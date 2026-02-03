<script setup lang="ts">
import { computed } from 'vue'

import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'

interface SheetDrawerProps {
  title: string
  description?: string
  open: boolean
  side?: 'top' | 'right' | 'bottom' | 'left'
  class?: string
}

const props = withDefaults(defineProps<SheetDrawerProps>(), {
  side: 'right',
  class: '',
})

const emit = defineEmits<{
  'close': []
  'update:open': [value: boolean]
}>()

const slots = defineSlots<{
  default?: () => any
  header?: () => any
}>()

const sheetOpen = computed({
  get: () => props.open,
  set: (value) => {
    emit('update:open', value)
    if (!value) {
      emit('close')
    }
  },
})

const sheetContentClass = computed(() => {
  const baseClasses = 'w-full shadow-xl'
  return props.class ? `${baseClasses} ${props.class}` : baseClasses
})
</script>

<template>
  <Sheet v-model:open="sheetOpen">
    <SheetContent
      :side="side"
      :class="sheetContentClass"
    >
      <div class="flex flex-col h-full">
        <SheetHeader v-if="!slots.header">
          <SheetTitle>
            {{ title }}
          </SheetTitle>
          <SheetDescription v-if="description">
            {{ description }}
          </SheetDescription>
        </SheetHeader>
        <slot v-else name="header" />

        <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-6">
          <slot />
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>
