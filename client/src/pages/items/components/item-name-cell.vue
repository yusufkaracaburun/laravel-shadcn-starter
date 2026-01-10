<script setup lang="ts">
import type { IItem } from '@/pages/items/models/items'

interface IProps {
  item: IItem
  name: string
}

const props = defineProps<IProps>()
const router = useRouter()

// Navigate to item detail page
function handleClick() {
  router.push({
    name: '/items/view/[id]',
    params: { id: props.item.id.toString() },
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
