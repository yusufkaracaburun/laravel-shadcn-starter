<script setup lang="ts">
import { ArrowLeft, Download, FilePenLine, Printer, Trash2 } from 'lucide-vue-next'
import { computed, ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { TInvoice } from '@/pages/invoices/data/schema'

import Error from '@/components/custom-error.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { StatusBadge } from '@/components/ui/status-badge'
import { useInvoices } from '@/composables/use-invoices'
import InvoiceDelete from '@/pages/invoices/components/invoice-delete.vue'
import { statuses } from '@/pages/invoices/data/data'
import {
  formatDateForPreview,
  formatDateTime,
  formatMoney,
  formatNumber,
} from '@/pages/invoices/utils/formatters'
import { getPaymentStatusColor } from '@/utils/status-colors'

const props = defineProps<{
  invoice: TInvoice | null
  invoiceId: number
}>()

const router = useRouter()

const showComponent = shallowRef<typeof InvoiceDelete | null>(null)
const isDialogOpen = ref(false)

const { downloadInvoicePdf } = useInvoices()

type TCommand = 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      router.push({ name: '/invoices/edit-[id]', params: { id: props.invoiceId.toString() } })
      break
    case 'delete':
      showComponent.value = InvoiceDelete
      isDialogOpen.value = true
      break
  }
}

function handleDeleteClose() {
  isDialogOpen.value = false
  // Navigate back to invoices list after deletion
  router.push('/invoices')
}

function printInvoice() {
  window.print()
}

function downloadPDF() {
  downloadInvoicePdf(props.invoiceId)
}
</script>

<template>
  <div class="print:hidden bg-white p-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button variant="outline" @click="router.back()">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <div class="flex items-center gap-2">
          <StatusBadge
            v-if="props.invoice"
            :status="props.invoice!.status"
            type="invoice"
            :icon="statuses.find((s) => s.value === props.invoice!.status)?.icon"
            :label="statuses.find((s) => s.value === props.invoice!.status)?.label"
          />
        </div>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" @click="printInvoice">
          <Printer class="mr-2 size-4" />
          Print
        </Button>
        <Button variant="outline" @click="downloadPDF">
          <Download class="mr-2 size-4" />
          Download PDF
        </Button>
        <Button v-if="props.invoice" variant="outline" @click="handleSelect('edit')">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button v-if="props.invoice" variant="destructive" @click="handleSelect('delete')">
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </div>
  </div>

  <div class="border-b border-gray-200" />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent v-if="showComponent && props.invoice" class="sm:max-w-[425px]">
      <InvoiceDelete
        v-if="showComponent === InvoiceDelete"
        :invoice="props.invoice"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
