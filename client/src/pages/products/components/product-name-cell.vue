<script setup lang="ts">
import type { IProduct } from '@/pages/products/models/products'

interface IProps {
  product: IProduct
  name: string
}

const props = defineProps<IProps>()
const router = useRouter()

// Navigate to product detail page
function handleClick() {
  router.push({
    name: '/products/view/[id]',
    params: { id: props.product.id.toString() },
  })
}
</script>

<template>
  <div class="flex items-center gap-3">
    <button
      class="max-w-[500px] truncate font-medium text-left hover:underline cursor-pointer focus:outline-none focus:underline"
      @click="handleClick"
    >
      {{ name }}
    </button>
  </div>
</template>
