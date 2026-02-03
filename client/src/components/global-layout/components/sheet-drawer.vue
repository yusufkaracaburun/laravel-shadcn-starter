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
  class: 'w-full shadow-xl border-l-0',
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
</script>

<template>
  <Sheet v-model:open="sheetOpen">
    <SheetContent :side="props.side" :class="props.class">
      <div class="flex flex-col h-full" data-testid="sheet-drawer">
        <SheetHeader v-if="!slots.header">
          <SheetTitle>
            {{ props.title }}
          </SheetTitle>
          <SheetDescription v-if="props.description">
            {{ props.description }}
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
