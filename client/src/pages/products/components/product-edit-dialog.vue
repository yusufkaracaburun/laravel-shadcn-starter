<script lang="ts" setup>
import type { IProduct } from '@/pages/products/models/products'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import ProductResourceDialog from './product-resource-dialog.vue'

interface IProductEditDialogProps {
  product: IProduct
  open: boolean
}

const props = defineProps<IProductEditDialogProps>()

const emits = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'close'): void
}>()

function handleClose() {
  emits('update:open', false)
  emits('close')
}
</script>

<template>
  <Dialog :open="props.open" @update:open="emits('update:open', $event)">
    <DialogContent
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <ProductResourceDialog :product="props.product" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
