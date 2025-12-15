<script lang="ts" setup>
import type { Item } from '../data/schema'

import ItemForm from './item-form.vue'

const props = defineProps<{
  item: Item | null
}>()
defineEmits(['close'])

const item = computed(() => props.item)
const title = computed(() => (item.value?.id ? 'Edit Item' : 'New Item'))
const description = computed(() =>
  item.value?.id ? `Edit item ${item.value.id}` : 'Create new item',
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
    <ItemForm class="mt-2" :item="item" @close="$emit('close')" />
  </div>
</template>
