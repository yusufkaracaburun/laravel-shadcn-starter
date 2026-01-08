<script lang="ts" setup>
import { useCustomers } from '@/composables/use-customers'

import type { Customer } from '../data/schema'

const props = defineProps<{
  customer: Customer
}>()

const emits = defineEmits<{
  close: []
}>()

const { deleteCustomer } = useCustomers()
const isDeleting = ref(false)

async function handleRemove() {
  if (!props.customer?.id) {
    return
  }

  try {
    isDeleting.value = true
    await deleteCustomer(props.customer.id)
    emits('close')
  } catch (error) {
    // Error handling is done in the composable
    console.error('Customer deletion error:', error)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Delete Customer</UiDialogTitle>
      <UiDialogDescription class="mt-2">
        Are you sure you want to delete <strong>{{ customer.name }}</strong>? This action cannot be undone.
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
        @click="handleRemove"
      >
        <UiSpinner v-if="isDeleting" class="mr-2" />
        Delete
      </UiButton>
    </UiDialogFooter>
  </div>
</template>
