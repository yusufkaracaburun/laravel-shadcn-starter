<script setup lang="ts">
import type { ComputedRef } from 'vue'

import { computed } from 'vue'

import type {
  IInvoice,
  IInvoiceActivity,
  IInvoiceEmail,
  IInvoicePayment,
} from '@/pages/invoices/models/invoice'

import { Accordion } from '@/components/ui/accordion'
import InvoiceActivityTimeline from '@/pages/invoices/view/components/invoice-activity-timeline.vue'
import InvoiceEmails from '@/pages/invoices/view/components/invoice-emails.vue'
import InvoicePayments from '@/pages/invoices/view/components/invoice-payments.vue'

const props = defineProps<{
  invoice: IInvoice
}>()

const invoiceActivities = computed(
  () => props.invoice.activities ?? [],
) as ComputedRef<IInvoiceActivity[]>
const invoicePayments = computed(
  () => props.invoice.payments ?? [],
) as ComputedRef<IInvoicePayment[]>
const invoiceEmails = computed(() => props.invoice.emails ?? []) as ComputedRef<
  IInvoiceEmail[]
>
</script>

<template>
  <div class="print:max-w-none print:mx-0 print:p-0 p-4">
    <Accordion type="single" collapsible class="w-full" default-value="item-activity">
      <InvoiceActivityTimeline :activities="invoiceActivities" />
      <InvoicePayments :payments="invoicePayments" />
      <InvoiceEmails :emails="invoiceEmails" />
    </Accordion>
  </div>
</template>
