<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'

import { Button } from '@/components/ui/button'

defineProps<{
  isLoading?: boolean
}>()

const showPreview = ref(true)

function togglePreview() {
  showPreview.value = !showPreview.value
}
</script>

<template>
  <div class="space-y-4">
    <!-- Action Buttons -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <slot name="actions" />
      </div>
      <div class="flex items-center gap-2">
        <slot name="header-actions" />
        <Button variant="outline" size="sm" class="hidden lg:flex" @click="togglePreview">
          <component :is="showPreview ? EyeOff : Eye" class="mr-2 size-4" />
          {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
        </Button>
      </div>
    </div>

    <!-- Split Layout -->
    <div class="grid gap-6" :class="showPreview ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'">
      <!-- Left Column: Form -->
      <div class="space-y-4">
        <slot name="form" />
      </div>

      <!-- Right Column: Preview -->
      <div v-if="showPreview" class="hidden lg:block">
        <slot name="preview" />
      </div>
    </div>

    <!-- Mobile Preview Toggle -->
    <div class="lg:hidden">
      <div v-if="showPreview" class="mt-4">
        <slot name="preview" />
      </div>
      <div class="mt-4 flex justify-center">
        <button class="text-sm text-muted-foreground hover:text-foreground" @click="togglePreview">
          {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
        </button>
      </div>
    </div>
  </div>
</template>
