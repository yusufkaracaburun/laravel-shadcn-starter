<script setup lang="ts">
import { computed } from 'vue'

import { cn } from '@/lib/utils'

const props = defineProps<{
  message?: string | string[] | null
}>()

// Handle API error format: string, array, or null
const displayMessage = computed(() => {
  if (!props.message)
    return null

  if (Array.isArray(props.message))
    return props.message[0] || null

  return props.message
})
</script>

<template>
  <p
    v-if="displayMessage"
    :class="cn('text-sm font-medium text-destructive')"
  >
    {{ displayMessage }}
  </p>
</template>

