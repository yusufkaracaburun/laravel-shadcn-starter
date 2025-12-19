<script setup lang="ts">
import { Mail } from 'lucide-vue-next'

import type { IInvoiceEmail } from '@/pages/invoices/models/invoice'

import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { Button } from '@/components/ui/button'
import { formatDateTime } from '@/pages/invoices/utils/formatters'

interface Props {
  invoiceEmails: IInvoiceEmail[]
}

const props = defineProps<Props>()

const emits = defineEmits<{
  (e: 'openEmailDialog', email: IInvoiceEmail): void
}>()

function openEmailDialog(email: IInvoiceEmail) {
  emits('openEmailDialog', email)
}
</script>

<template>
  <AccordionItem value="item-emails">
    <AccordionTrigger
      class="flex items-center justify-between cursor-pointer select-none"
    >
      <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
        <Mail class="w-4 h-4 text-gray-600" />
        Emails
      </h4>
    </AccordionTrigger>
    <AccordionContent class="pt-2 border-t border-gray-200">
      <div v-if="props.invoiceEmails.length === 0" class="text-center py-8">
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
          v-for="email in props.invoiceEmails"
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
</template>
