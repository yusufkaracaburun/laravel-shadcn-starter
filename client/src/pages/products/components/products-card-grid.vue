<script setup lang="ts">
import type { IProduct } from '@/pages/products/models/products'

import Loading from '@/components/loading.vue'

import ProductCard from './product-card.vue'

interface Props {
  products: IProduct[]
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
  <div
    v-else-if="products.length === 0"
    class="flex items-center justify-center min-h-[400px]"
  >
    <div class="text-center">
      <p class="text-muted-foreground">
        No products found.
      </p>
    </div>
  </div>
  <div
    v-else
    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
  >
    <ProductCard v-for="product in products" :key="product.id" :product="product" />
  </div>
</template>
