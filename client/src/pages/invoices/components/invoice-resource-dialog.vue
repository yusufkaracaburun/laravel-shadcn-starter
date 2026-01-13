<script lang="ts" setup>
import type { IInvoice } from '../models/invoice'

import InvoiceForm from './invoice-form.vue'

const props = defineProps<{
  invoice: IInvoice | null
}>()
defineEmits(['close'])

const invoice = computed(() => props.invoice)
const title = computed(() =>
  invoice.value?.id ? 'Edit Invoice' : 'New Invoice',
)
const description = computed(() =>
  invoice.value?.id
    ? `Edit invoice ${invoice.value.invoice_number || invoice.value.id}`
    : 'Create new invoice',
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
    <InvoiceForm class="mt-2" :invoice="invoice" @close="$emit('close')" />
  </div>
</template>
