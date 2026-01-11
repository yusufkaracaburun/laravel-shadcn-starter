<script lang="ts" setup>
import type { IProduct } from '../models/products'

import ProductForm from './product-form.vue'

const props = defineProps<{
  product?: IProduct | null
}>()

defineEmits(['close'])

const title = computed(() => (props.product ? 'Edit Product' : 'Create New Product'))
const description = computed(() =>
  props.product
    ? `Update product information for ${props.product.name}.`
    : 'Add a new product to the system. Fill in the required information below.',
)
</script>

<template>
  <UiDrawerHeader>
    <UiDrawerTitle>
      {{ title }}
    </UiDrawerTitle>
    <UiDrawerDescription>
      {{ description }}
    </UiDrawerDescription>
  </UiDrawerHeader>

  <ProductForm :product="product" class="mt-4" @close="$emit('close')" />
</template>
