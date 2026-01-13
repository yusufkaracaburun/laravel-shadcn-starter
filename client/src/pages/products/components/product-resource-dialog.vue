<script lang="ts" setup>
import type { IProduct } from '@/pages/products/models/products'

import ProductForm from './product-form.vue'

interface IProductResourceDialogProps {
  product: IProduct | null
}

const props = defineProps<IProductResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.product ? 'Edit Product' : 'New Product'))
const description = computed(() =>
  props.product
    ? `Update product information for ${props.product.name}.`
    : 'Add a new product to the system. Fill in the required information below.',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <ProductForm :product="product" class="mt-2" @close="emits('close')" />
  </div>
</template>
