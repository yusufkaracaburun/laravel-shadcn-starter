<script lang="ts" setup>
import type { Item } from '../data/schema'

import ItemForm from './item-form.vue'

const props = defineProps<{
  item?: Item | null
}>()

defineEmits(['close'])

const title = computed(() => (props.item ? 'Edit Item' : 'Create New Item'))
const description = computed(() =>
  props.item
    ? `Update item information for ${props.item.name}.`
    : 'Add a new item to the system. Fill in the required information below.',
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

  <ItemForm :item="item" class="mt-4" @close="$emit('close')" />
</template>

