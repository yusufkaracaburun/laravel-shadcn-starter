<script setup lang="ts">
import { CreditCard, Hash } from 'lucide-vue-next'

import type { IInvoicePayment } from '@/pages/invoices/models/invoice'

import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { paymentStatuses } from '@/pages/invoices/data/data'
import { formatDateTime } from '@/pages/invoices/utils/formatters'
import { formatMoney } from '@/utils/money'
import { getPaymentStatusColor } from '@/utils/status-colors'

interface Props {
  payments: IInvoicePayment[]
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionItem value="item-payments">
    <AccordionTrigger
      class="flex items-center justify-between cursor-pointer select-none"
    >
      <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
        <CreditCard class="w-4 h-4 text-gray-600" />
        Payments
      </h4>
    </AccordionTrigger>
    <AccordionContent class="pt-2 border-t border-gray-200">
      <div v-if="props.payments.length === 0" class="text-center py-8">
        <div
          class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3"
        >
          <CreditCard class="w-6 h-6 text-gray-400" />
        </div>
        <p class="text-sm text-gray-500">
          No payments yet
        </p>
      </div>

      <div v-else class="px-1">
        <div
          v-for="payment in props.payments"
          :key="payment.id"
          class="relative flex gap-3 pb-3 last:pb-0"
        >
          <!-- Payment content -->
          <div class="flex-1 min-w-0">
            <div class="bg-white rounded-lg border border-gray-100 p-3">
              <div class="flex justify-between items-start mb-1">
                <span class="text-sm text-gray-900 font-bold mb-1">{{
                  formatMoney(payment.amount)
                }}</span>
                <div
                  :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPaymentStatusColor(payment.status)}`"
                >
                  <component
                    :is="
                      paymentStatuses.find((s) => s.value === payment.status)
                        ?.icon
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

                      <span class="font-medium">Amount:</span>
                      <span>{{ formatMoney(payment.amount) }}</span>

                      <span v-if="payment.method" class="font-medium">Method:</span>
                      <span v-if="payment.method">{{ payment.method }}</span>

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

                      <span v-if="payment.paid_at" class="font-medium">Paid At:</span>
                      <span v-if="payment.paid_at">{{
                        formatDateTime(payment.paid_at)
                      }}</span>

                      <span v-if="payment.refunded_at" class="font-medium">Refunded At:</span>
                      <span v-if="payment.refunded_at">{{
                        formatDateTime(payment.refunded_at)
                      }}</span>
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
</template>
