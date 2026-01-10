<script lang="ts" setup>
import type { IItem } from '@/pages/items/models/items'

import ItemForm from './item-form.vue'

interface IItemResourceDialogProps {
  item: IItem | null
}

const props = defineProps<IItemResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.item ? 'Edit Item' : 'New Item'))
const description = computed(() =>
  props.item
    ? `Update item information for ${props.item.name}.`
    : 'Add a new item to the system. Fill in the required information below.',
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
    <ItemForm :item="item" class="mt-2" @close="emits('close')" />
  </div>
</template>
