<script lang="ts" setup>
import { useItems } from '@/composables/use-items'

import type { Item } from '../data/schema'

const props = defineProps<{
  item: Item
}>()

const emits = defineEmits<{
  close: []
}>()

const { deleteItem } = useItems()
const isDeleting = ref(false)

async function handleRemove() {
  if (!props.item?.id) {
    return
  }

  try {
    isDeleting.value = true
    await deleteItem(props.item.id)
    emits('close')
  } catch (error) {
    // Error handling is done in the composable
    console.error('Item deletion error:', error)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Delete Item</UiDialogTitle>
      <UiDialogDescription class="mt-2">
        Are you sure you want to delete <strong>{{ item.name }}</strong
        >? This action cannot be undone.
      </UiDialogDescription>
    </UiDialogHeader>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>
      <UiButton variant="destructive" :disabled="isDeleting" @click="handleRemove">
        <UiSpinner v-if="isDeleting" class="mr-2" />
        Delete
      </UiButton>
    </UiDialogFooter>
  </div>
</template>
