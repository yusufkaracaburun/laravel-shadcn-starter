<script setup lang="ts">
import type { IProduct } from '@/pages/products/models/products'

import { PackageIcon } from '@/composables/use-icons.composable'

interface Props {
  product: IProduct
}

defineProps<Props>()
</script>

<template>
  <div class="flex items-start gap-6">
    <div
      class="flex size-20 items-center justify-center rounded-full bg-primary/10 ring-4 ring-background shadow-lg"
    >
      <PackageIcon class="size-10 text-primary" />
    </div>
    <div class="flex-1 min-w-0">
      <h1 class="text-3xl font-bold tracking-tight mb-2">
        {{ product.name }}
      </h1>
      <div v-if="product.description" class="text-base text-muted-foreground">
        {{ product.description }}
      </div>
    </div>
  </div>
</template>
