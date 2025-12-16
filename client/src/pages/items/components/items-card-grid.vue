<script setup lang="ts">
import type { Item } from '@/services/items.service'

import Loading from '@/components/loading.vue'

import ItemCard from './item-card.vue'

interface Props {
  items: Item[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})
</script>

<template>
  <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
    <Loading />
  </div>
  <div v-else-if="items.length === 0" class="flex items-center justify-center min-h-[400px]">
    <div class="text-center">
      <p class="text-muted-foreground">No items found.</p>
    </div>
  </div>
  <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <ItemCard v-for="item in items" :key="item.id" :item="item" />
  </div>
</template>
