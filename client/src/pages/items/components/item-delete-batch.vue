<script lang="ts" setup generic="T = IItem">
import type { Table as VueTable } from '@tanstack/vue-table'

import { toast } from 'vue-sonner'

import ConfirmDialog from '@/components/confirm-dialog.vue'
import { useItems } from '@/composables/use-items.composable'

import type { IItem } from '../models/items'

const { table } = defineProps<{
  table: VueTable<T>
}>()

const openModel = defineModel<boolean>('open', {
  default: false,
})

const { deleteItem } = useItems()

const CONFIRM_WORD = 'DELETE'

const confirmValue = ref('')
const isDeleting = ref(false)

const selectedRows = computed(() => table.getSelectedRowModel().rows)
const selectedCount = computed(() => selectedRows.value.length || 0)

async function handleConfirm() {
  if (confirmValue.value !== CONFIRM_WORD) {
    toast.error(`Please type "${CONFIRM_WORD}" to confirm deletion.`)
    return
  }

  const rowsToDelete = selectedRows.value
  const itemIds = rowsToDelete
    .map(row => (row.original as IItem).id)
    .filter(id => id != null)

  if (itemIds.length === 0) {
    toast.error('No valid items selected for deletion.')
    return
  }

  try {
    isDeleting.value = true

    // Delete all selected items
    await Promise.all(itemIds.map(id => deleteItem(id)))

    table.resetRowSelection()
    openModel.value = false
    confirmValue.value = ''
  } catch (error) {
    // Error handling is done in the composable
    console.error('Batch item deletion error:', error)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <ConfirmDialog
    v-model:open="openModel"
    confirm-button-text="Delete"
    destructive
    :disabled="confirmValue.trim() !== CONFIRM_WORD || isDeleting"
    @confirm="handleConfirm"
  >
    <template #title>
      Delete {{ selectedCount }} items?
    </template>
    <template #description>
      Are you sure you want to delete the selected items? <br>
      This action cannot be undone.
    </template>

    <template #default>
      <UiLabel class="my-4 flex flex-col items-start gap-1.5">
        <span>Confirm by typing {{ CONFIRM_WORD }}:</span>
        <UiInput
          v-model="confirmValue"
          :placeholder="`Type &quot;${CONFIRM_WORD}&quot; to confirm.`"
        />
      </UiLabel>

      <UiAlert variant="destructive">
        <UiAlertTitle>Warning!</UiAlertTitle>
        <UiAlertDescription>
          Please be careful, this operation can not be rolled back.
        </UiAlertDescription>
      </UiAlert>
    </template>
  </ConfirmDialog>
</template>
