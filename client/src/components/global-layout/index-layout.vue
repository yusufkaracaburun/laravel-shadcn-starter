<script lang="ts" setup>
import { ref } from 'vue'

import { Sheet, SheetContent } from '@/components/ui/sheet'

import type { LayoutHeaderProps } from './types'

import BasicHeader from './basic-header.vue'

defineProps<LayoutHeaderProps>()

const emit = defineEmits<{
  closeSidebar: []
}>()

const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex flex-row h-screen gap-4">
    <main class="flex flex-col h-screen w-full">
      <BasicHeader :title="title" :description="description" :sticky="sticky">
        <template #actions>
          <slot name="actions" />
        </template>
      </BasicHeader>
      <main class="py-4 overflow-y-auto">
        <slot />
      </main>
    </main>

    <!-- Sheet component for both mobile and desktop - always rendered -->
    <Sheet
      :open="$slots.sidebar ? true : sidebarOpen"
      @update:open="(value) => {
        sidebarOpen = value
        if (!value) {
          // Emit close event when Sheet is closed
          emit('closeSidebar')
        }
      }"
    >
      <SheetContent
        v-if="$slots.sidebar"
        side="right"
        class="w-full shadow-xl"
      >
        <slot name="sidebar" />
      </SheetContent>
    </Sheet>
  </div>
</template>
