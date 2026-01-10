<script setup lang="ts">
import type { IItem } from '@/pages/items/models/items'

import { useItems } from '@/composables/use-items.composable'

interface IItemDeleteProps {
  item: IItem
}

const props = defineProps<IItemDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteItem } = useItems()

async function handleRemove() {
  await deleteItem(props.item.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle> Delete this item: {{ item.name }} ? </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete an item with the ID {{ item.id }}. This action
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
