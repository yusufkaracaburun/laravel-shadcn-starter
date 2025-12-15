<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
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
      </div>
    </div>

    <!-- Split Layout -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
        <button
          class="text-sm text-muted-foreground hover:text-foreground"
          @click="togglePreview"
        >
          {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
        </button>
      </div>
    </div>
  </div>
</template>

