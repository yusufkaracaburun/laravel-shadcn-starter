<script lang="ts" setup>
import { useInvoices } from '@/composables/use-invoices'

import type { TInvoice } from '../data/schema'

const props = defineProps<{
  invoice: TInvoice
}>()

const emits = defineEmits<{
  close: []
}>()

const { deleteInvoice } = useInvoices()
const isDeleting = ref(false)

async function handleRemove() {
  if (!props.invoice?.id) {
    return
  }

  try {
    isDeleting.value = true
    await deleteInvoice(props.invoice.id)
    emits('close')
  }
  catch (error) {
    // Error handling is done in the composable
    console.error('Invoice deletion error:', error)
  }
  finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Delete Invoice</UiDialogTitle>
      <UiDialogDescription class="mt-2">
        Are you sure you want to delete invoice
        <strong>{{ invoice.invoice_number || `#${invoice.id}` }}</strong>? This action cannot be undone.
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
