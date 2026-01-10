<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { useCustomers } from '@/composables/use-customers.composable'

import type { ICustomer } from '../models/customers'

interface Props {
  table: Table<ICustomer>
  open?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
})

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.open,
  set: value => emit('update:open', value),
})

const { deleteCustomer } = useCustomers()
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
      deleteCustomer(row.original.id),
    )
    await Promise.all(deletePromises)
    props.table.resetRowSelection()
    isOpen.value = false
  } catch (error) {
    // Error handling is done in the composable
    console.error('Batch customer deletion error:', error)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDialogContent class="sm:max-w-[425px]">
      <UiDialogHeader>
        <UiDialogTitle>Delete Customers</UiDialogTitle>
        <UiDialogDescription class="mt-2">
          Are you sure you want to delete
          <strong>{{ selectedCount }}</strong> customer(s)? This action cannot
          be undone.
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
