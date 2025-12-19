<script setup lang="ts">
import { Clock, CreditCard, Hash, Mail, User } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import type {
  IInvoice,
  IInvoiceActivity,
  IInvoiceEmail,
  IInvoicePayment,
} from '@/pages/invoices/models/invoice'

import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { statuses } from '@/pages/invoices/data/data'
import {
  formatDateForPreview,
  formatDateTime,
  formatMoney,
} from '@/pages/invoices/utils/formatters'
import SentEmailDialog from '@/pages/invoices/view/components/sent-email-dialog.vue'
import { getPaymentStatusColor } from '@/utils/status-colors'

const props = defineProps<{
  invoice: IInvoice
}>()

const isEmailDialogOpened = ref(false)
const selectedEmail = ref<IInvoiceEmail | null>(null)

// Activity logs for the timeline sidebar
const invoiceActivities = computed<IInvoiceActivity[]>(() => {
  if (!props.invoice?.activities) {
    return []
  }
  return Array.isArray(props.invoice.activities) ? props.invoice.activities : []
})

const invoicePayments = computed<IInvoicePayment[]>(() => {
  if (!props.invoice?.payments) {
    return []
  }
  return Array.isArray(props.invoice.payments) ? props.invoice.payments : []
})

const invoiceEmails = computed<IInvoiceEmail[]>(() => {
  if (!props.invoice?.emails) {
    return []
  }
  return Array.isArray(props.invoice.emails) ? props.invoice.emails : []
})

function formatDate(dateString: string | null): string {
  if (!dateString) {
    return '—'
  }
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      return formatDateForPreview(dateString)
    }
  }
  catch {
    // Ignore parsing errors
  }
  return dateString
}

function formatCurrency(value: any): string {
  return formatMoney(value)
}

function openEmailDialog(email: IInvoiceEmail) {
  selectedEmail.value = email
  isEmailDialogOpened.value = true
}

function closeEmailDialog() {
  isEmailDialogOpened.value = false
  selectedEmail.value = null
}
</script>

<template>
  <div class="print:max-w-none print:mx-0 print:p-0 p-4">
    <Accordion
      type="single"
      collapsible
      class="w-full"
      default-value="item-activity"
    >
      <AccordionItem value="item-activity">
        <AccordionTrigger
          class="flex items-center justify-between cursor-pointer select-none"
        >
          <h4
            class="text-base font-semibold text-gray-900 flex items-center gap-2"
          >
            <Clock class="w-4 h-4 text-gray-600" />
            Activity Timeline
          </h4>
        </AccordionTrigger>
        <AccordionContent class="pt-2 border-t border-gray-200">
          <div v-if="invoiceActivities.length === 0" class="text-center py-8">
            <div
              class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3"
            >
              <Clock class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm text-gray-500">
              No activity yet
            </p>
          </div>

          <div v-else class="px-1">
            <div
              v-for="(activity, index) in invoiceActivities"
              :key="activity.id"
              class="relative flex gap-3 pb-3 last:pb-0"
            >
              <!-- Activity content -->
              <div class="flex-1 min-w-0">
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                  <div class="flex justify-between items-start mb-1">
                    <span class="text-sm text-gray-900 font-bold mb-1">{{
                      activity.description
                    }}</span>
                  </div>

                  <div
                    class="flex justify-between items-start mb-1 text-xs text-gray-500"
                  >
                    <div class="flex items-center gap-2">
                      <User class="w-3 h-3" />
                      <span>{{ activity.causer?.name || 'System' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="text-gray-300">•</span>
                      <span>{{ formatDateTime(activity.created_at) }}</span>
                    </div>
                  </div>

                  <!-- Show properties changes if available -->
                  <div
                    v-if="
                      activity.properties
                        && Object.keys(activity.properties.attributes || {}).length
                          > 0
                    "
                    class="mt-2 pt-2 border-t border-gray-100"
                  >
                    <details
                      class="text-xs rounded-md border border-gray-200 bg-white p-1 mt-2"
                    >
                      <summary
                        class="cursor-pointer text-gray-700 hover:text-gray-900 font-medium p-1 -m-1"
                      >
                        View changes
                      </summary>
                      <div class="mt-2 space-y-1">
                        <div
                          v-for="(value, key) in activity.properties.attributes"
                          :key="key"
                          class="text-gray-700"
                        >
                          <span class="font-medium">{{ key.replace(/_/g, ' ') }}:</span>
                          <TooltipProvider>
                            <Tooltip>
                              <TooltipTrigger as-child>
                                <span class="ml-1">{{
                                  typeof value === 'object'
                                    ? JSON.stringify(value)
                                    : value
                                }}</span>
                              </TooltipTrigger>
                              <TooltipContent>
                                <p>
                                  Old Value:
                                  {{ activity.properties.old?.[key] }}
                                </p>
                              </TooltipContent>
                            </Tooltip>
                          </TooltipProvider>
                        </div>
                      </div>
                    </details>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="item-payments">
        <AccordionTrigger
          class="flex items-center justify-between cursor-pointer select-none"
        >
          <h4
            class="text-base font-semibold text-gray-900 flex items-center gap-2"
          >
            <CreditCard class="w-4 h-4 text-gray-600" />
            Payments
          </h4>
        </AccordionTrigger>
        <AccordionContent class="pt-2 border-t border-gray-200">
          <div v-if="invoicePayments.length === 0" class="text-center py-8">
            <div
              class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3"
            >
              <Clock class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm text-gray-500">
              No payments yet
            </p>
          </div>

          <div v-else class="px-1">
            <div
              v-for="payment in invoicePayments"
              :key="payment.id"
              class="relative flex gap-3 pb-3 last:pb-0"
            >
              <!-- Payment content -->
              <div class="flex-1 min-w-0">
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                  <div class="flex justify-between items-start mb-1">
                    <span class="text-sm text-gray-900 font-bold mb-1">{{
                      formatCurrency(payment.amount)
                    }}</span>
                    <div
                      :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPaymentStatusColor(payment.status)}`"
                    >
                      <component
                        :is="
                          statuses.find((s) => s.value === payment.status)?.icon
                        "
                        class="w-3 h-3 mr-1"
                      />
                      {{ payment.status_formatted?.label || payment.status }}
                    </div>
                  </div>

                  <div
                    class="flex justify-between items-start mb-1 text-xs text-gray-500"
                  >
                    <div class="flex items-center gap-2">
                      <Hash class="w-3 h-3" />
                      <span>{{ payment.payment_number }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="text-gray-300">•</span>
                      <span>{{ formatDateTime(payment.date) }}</span>
                    </div>
                  </div>

                  <!-- Show properties changes if available -->
                  <div class="mt-2 pt-2 border-t border-gray-100">
                    <details
                      class="text-xs rounded-md border border-gray-200 bg-white p-1 mt-2"
                    >
                      <summary
                        class="cursor-pointer text-gray-700 hover:text-gray-900 font-medium p-1 -m-1"
                      >
                        View details
                      </summary>
                      <div class="mt-2 space-y-1 text-gray-700">
                        <div class="grid grid-cols-[auto_1fr] gap-x-2">
                          <span class="font-medium">ID:</span>
                          <span>{{ payment.id }}</span>

                          <span
                            v-if="payment.payment_number"
                            class="font-medium"
                          >Payment Number:</span>
                          <span v-if="payment.payment_number">{{
                            payment.payment_number
                          }}</span>

                          <span class="font-medium">Date:</span>
                          <span>{{ formatDate(payment.date) }}</span>

                          <span class="font-medium">Amount:</span>
                          <span>{{ formatCurrency(payment.amount) }}</span>

                          <span v-if="payment.method" class="font-medium">Method:</span>
                          <span v-if="payment.method">{{
                            payment.method
                          }}</span>

                          <span v-if="payment.provider" class="font-medium">Provider:</span>
                          <span v-if="payment.provider">{{
                            payment.provider
                          }}</span>

                          <span
                            v-if="payment.provider_reference"
                            class="font-medium"
                          >Provider Reference:</span>
                          <span v-if="payment.provider_reference">{{
                            payment.provider_reference
                          }}</span>

                          <span class="font-medium">Status:</span>
                          <span>{{
                            payment.status_formatted?.label || payment.status
                          }}</span>

                          <span v-if="payment.paid_at" class="font-medium">Paid At:</span>
                          <span v-if="payment.paid_at">{{
                            formatDateTime(payment.paid_at)
                          }}</span>

                          <span v-if="payment.refunded_at" class="font-medium">Refunded At:</span>
                          <span v-if="payment.refunded_at">{{
                            formatDateTime(payment.refunded_at)
                          }}</span>

                          <span class="font-medium">Created At:</span>
                          <span>{{ formatDateTime(payment.created_at) }}</span>

                          <span class="font-medium">Updated At:</span>
                          <span>{{ formatDateTime(payment.updated_at) }}</span>
                        </div>
                      </div>
                    </details>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <AccordionItem value="item-emails">
        <AccordionTrigger
          class="flex items-center justify-between cursor-pointer select-none"
        >
          <h4
            class="text-base font-semibold text-gray-900 flex items-center gap-2"
          >
            <Mail class="w-4 h-4 text-gray-600" />
            Emails
          </h4>
        </AccordionTrigger>
        <AccordionContent class="pt-2 border-t border-gray-200">
          <div v-if="invoiceEmails.length === 0" class="text-center py-8">
            <div
              class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3"
            >
              <Mail class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm text-gray-500">
              No emails yet
            </p>
          </div>
          <div v-else class="px-1">
            <div
              v-for="email in invoiceEmails"
              :key="email.id"
              class="relative flex gap-3 pb-3 last:pb-0"
            >
              <div class="flex-1 min-w-0">
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                  <div class="flex justify-between items-start mb-1">
                    <span class="text-sm text-gray-900 font-bold mb-1">{{
                      email.subject || 'No Subject'
                    }}</span>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                      <span class="text-gray-300">•</span>
                      <span>{{ formatDateTime(email.created_at) }}</span>
                    </div>
                  </div>

                  <div class="mt-2 pt-2 border-t border-gray-100">
                    <details
                      class="text-xs rounded-md border border-gray-200 bg-white p-1 mt-2"
                    >
                      <summary
                        class="cursor-pointer text-gray-700 hover:text-gray-900 font-medium p-1 -m-1"
                      >
                        View details
                      </summary>
                      <div class="mt-2 space-y-1 text-gray-700">
                        <div class="grid grid-cols-[auto_1fr] gap-x-2">
                          <span class="font-medium">Subject:</span>
                          <span>{{ email.subject || 'N/A' }}</span>

                          <span class="font-medium">From:</span>
                          <span>{{ email.sender_email || 'N/A' }}</span>

                          <span class="font-medium">To:</span>
                          <span>{{ email.recipient_email || 'N/A' }}</span>

                          <span class="font-medium">Created At:</span>
                          <span>{{ formatDateTime(email.created_at) }}</span>

                          <span class="font-medium">Updated At:</span>
                          <span>{{ formatDateTime(email.updated_at) }}</span>
                        </div>
                      </div>
                      <div class="mt-4 pt-4 border-t border-gray-100">
                        <Button
                          variant="outline"
                          size="sm"
                          @click="openEmailDialog(email)"
                        >
                          View Full Email
                        </Button>
                      </div>
                    </details>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>

    <SentEmailDialog
      :is-open="isEmailDialogOpened"
      :email="selectedEmail"
      @close="closeEmailDialog"
    />
  </div>
</template>
