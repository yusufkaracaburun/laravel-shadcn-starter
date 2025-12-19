<script setup lang="ts">
import type { ComputedRef } from 'vue'

import { computed, ref } from 'vue'

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
import SentEmailDialog from '@/pages/invoices/view/components/sent-email-dialog.vue'

const props = defineProps<{
  invoice: IInvoice
}>()

const isEmailDialogOpened = ref(false)
const selectedEmail = ref<IInvoiceEmail | null>(null)
function openEmailDialog(email: IInvoiceEmail) {
  selectedEmail.value = email
  isEmailDialogOpened.value = true
}

function closeEmailDialog() {
  isEmailDialogOpened.value = false
  selectedEmail.value = null
}

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
    <Accordion
      type="single"
      collapsible
      class="w-full"
      default-value="item-activity"
    >
      <InvoiceActivityTimeline :invoice-activities="invoiceActivities" />
      <InvoicePayments :invoice-payments="invoicePayments" />
      <InvoiceEmails
        :invoice-emails="invoiceEmails"
        @open-email-dialog="openEmailDialog"
      />
    </Accordion>

    <SentEmailDialog
      :is-open="isEmailDialogOpened"
      :email="selectedEmail"
      @close="closeEmailDialog"
    />
  </div>
</template>
