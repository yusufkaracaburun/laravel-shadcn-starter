<script setup lang="ts">
import type { IInvoiceEmail } from '@/services/invoices.service'

import { XIcon } from '@/composables/use-icons'

import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { formatDateTime } from '@/pages/invoices/utils/formatters'

const props = defineProps<{
  isOpen: boolean
  email: IInvoiceEmail | null
}>()

const emit = defineEmits<{ (e: 'close'): void }>()

function closeDialog() {
  emit('close')
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="closeDialog">
    <DialogContent class="max-w-4xl p-0">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-2xl font-bold text-gray-900">
          Email Details
        </DialogTitle>
        <DialogClose variant="ghost" size="icon" class="absolute top-4 right-4">
          <XIcon class="w-4 h-4" />
        </DialogClose>
      </DialogHeader>

      <div
        v-if="email"
        class="p-6 space-y-4 text-gray-700 max-h-[80vh] overflow-y-auto"
      >
        <div
          class="flex flex-col sm:flex-row sm:items-center justify-between text-sm border-b pb-4"
        >
          <div class="flex items-center gap-2">
            <span class="font-semibold">From:</span>
            <span
              >{{ email.sender_name }} &lt;{{ email.sender_email }}&gt;</span
            >
          </div>
          <span class="text-xs text-gray-500">
            {{ formatDateTime(email.created_at) }}
          </span>
        </div>

        <div class="text-sm flex items-center gap-2">
          <span class="font-semibold">To:</span>
          <span>{{ email.recipient_email || 'N/A' }}</span>
        </div>

        <div class="text-sm flex items-center gap-2">
          <span class="font-semibold">Subject:</span>
          <span>{{ email.subject || 'N/A' }}</span>
        </div>

        <div class="border-t pt-4">
          <h3 class="font-semibold text-lg mb-2">Content:</h3>
          <div class="bg-gray-50 p-4 rounded-md overflow-x-auto">
            <div class="prose max-w-none" v-html="email.content" />
          </div>
        </div>
      </div>

      <div v-else class="p-6 text-center text-gray-500">
        <p>No email selected.</p>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Add any specific styles for the dialog here if needed */
</style>
