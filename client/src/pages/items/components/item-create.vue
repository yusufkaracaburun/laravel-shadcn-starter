<script lang="ts" setup>
import { PackagePlus } from 'lucide-vue-next'

import ItemResource from './item-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <PackagePlus />
        Create Item
      </UiButton>
    </UiDrawerTrigger>

    <UiDrawerContent class="px-4 pb-4">
      <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
        <ItemResource @close="isOpen = false" />
      </div>
    </UiDrawerContent>
  </UiDrawer>
</template>

