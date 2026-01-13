<script setup lang="ts">
import type { IProduct } from '@/pages/products/models/products'

import { useProducts } from '@/pages/products/composables/use-products.composable'

interface IProductDeleteProps {
  product: IProduct
}

const props = defineProps<IProductDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteProduct } = useProducts()

async function handleRemove() {
  await deleteProduct(props.product.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle> Delete this product: {{ product.name }} ? </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a product with the ID {{ product.id }}. This action
      cannot be undone.
    </UiDialogDescription>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>
      <UiDialogClose as-child>
        <UiButton variant="destructive" @click="handleRemove">
          Delete
        </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>
