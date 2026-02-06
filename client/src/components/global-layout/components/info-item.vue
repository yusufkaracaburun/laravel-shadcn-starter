<script setup lang="ts">
import type { Component } from 'vue'

interface InfoItemProps {
  label: string
  value: string | number | null | undefined
  icon?: Component
}

const props = defineProps<InfoItemProps>()

const slots = defineSlots<{
  value?: () => any
}>()
</script>

<template>
  <div
    v-if="props.value !== null && props.value !== undefined && props.value !== ''"
    class="flex items-center justify-between gap-2"
  >
    <div class="flex items-center gap-2">
      <component
        v-if="props.icon"
        :is="props.icon"
        class="size-4 text-muted-foreground"
      />
      <span class="text-sm text-muted-foreground">{{ props.label }}</span>
    </div>
    <div v-if="slots.value" class="text-sm font-medium">
      <slot name="value" />
    </div>
    <span v-else class="text-sm font-medium">
      {{ props.value }}
    </span>
  </div>
</template>
