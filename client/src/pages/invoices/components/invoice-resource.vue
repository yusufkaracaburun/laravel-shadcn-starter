<script lang="ts" setup>
import type { TInvoice } from '../data/schema'

import InvoiceForm from './invoice-form.vue'

const props = defineProps<{
  invoice?: TInvoice | null
}>()

defineEmits(['close'])

const title = computed(() => (props.invoice ? 'Edit Invoice' : 'Create New Invoice'))
const description = computed(() =>
  props.invoice
    ? `Update invoice information${props.invoice.invoice_number ? ` for ${props.invoice.invoice_number}` : ''}.`
    : 'Add a new invoice to the system. Fill in the required information below.',
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

  <InvoiceForm :invoice="invoice" class="mt-4" @close="$emit('close')" />
</template>
