<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { useInvoices } from '@/pages/invoices/composables/use-invoices.composable'

import type { IInvoice } from '../models/invoice'

interface IProps {
  table: Table<IInvoice>
  open?: boolean
}

const props = withDefaults(defineProps<IProps>(), {
  open: false,
})

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.open,
  set: value => emit('update:open', value),
})

const { deleteInvoice } = useInvoices()
const isDeleting = ref(false)

const selectedRows = computed(
  () => props.table.getFilteredSelectedRowModel().rows,
)
const selectedCount = computed(() => selectedRows.value.length)

async function handleBatchDelete() {
  if (selectedCount.value === 0) {
    return
  }

  try {
    isDeleting.value = true
    const deletePromises = selectedRows.value.map(row =>
      deleteInvoice(row.original.id),
    )
    await Promise.all(deletePromises)
    props.table.resetRowSelection()
    isOpen.value = false
  } catch (error) {
    // Error handling is done in the composable
    console.error('Batch invoice deletion error:', error)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDialogContent class="sm:max-w-[425px]">
      <UiDialogHeader>
        <UiDialogTitle>Delete Invoices</UiDialogTitle>
        <UiDialogDescription class="mt-2">
          Are you sure you want to delete
          <strong>{{ selectedCount }}</strong> invoice(s)? This action cannot be
          undone.
        </UiDialogDescription>
      </UiDialogHeader>
      <UiDialogFooter>
        <UiDialogClose as-child>
          <UiButton variant="outline">
            Cancel
          </UiButton>
        </UiDialogClose>
        <UiButton
          variant="destructive"
          :disabled="isDeleting"
          @click="handleBatchDelete"
        >
          <UiSpinner v-if="isDeleting" class="mr-2" />
          Delete
        </UiButton>
      </UiDialogFooter>
    </UiDialogContent>
  </UiDialog>
</template>
