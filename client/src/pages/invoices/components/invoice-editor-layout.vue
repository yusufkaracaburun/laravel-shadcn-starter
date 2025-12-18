<script setup lang="ts">
defineProps<{
  isLoading?: boolean
}>()
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
    <div class="grid gap-6 grid-cols-1">
      <!-- Left Column: Form -->
      <div class="space-y-4">
        <slot name="form" />
      </div>
    </div>
  </div>
</template>
