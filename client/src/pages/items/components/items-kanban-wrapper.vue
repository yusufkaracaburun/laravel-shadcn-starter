<script setup lang="ts">
import type { Item } from '../data/schema'

interface Props {
  items: Item[]
}

const props = defineProps<Props>()
</script>

<template>
  <div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center">
      <p class="text-muted-foreground">Kanban view is not available for items.</p>
      <p class="text-sm text-muted-foreground mt-2">Items don't have status fields to organize in a kanban board.</p>
    </div>
  </div>
</template>

