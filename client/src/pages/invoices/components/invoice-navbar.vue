<script setup lang="ts">
import { ArrowLeft, Download, FilePenLine, Trash2 } from 'lucide-vue-next'
import { ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { TInvoice } from '@/pages/invoices/data/schema'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { useInvoices } from '@/composables/use-invoices'
import InvoiceDelete from '@/pages/invoices/components/invoice-delete.vue'

const props = defineProps<{
  invoice: TInvoice | null
  invoiceId: number
}>()

const router = useRouter()

const showComponent = shallowRef<typeof InvoiceDelete | null>(null)
const isDialogOpen = ref(false)

const { downloadInvoicePdf } = useInvoices()

type TCommand = 'edit' | 'delete' | 'close' | 'download' | 'back'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      router.push({ name: '/invoices/edit/[id]', params: { id: props.invoiceId.toString() } })
      break
    case 'delete':
      showComponent.value = InvoiceDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      router.push('/invoices')
      break
    case 'download':
      downloadInvoicePdf(props.invoiceId)
      break
    case 'back':
      router.back()
      break
  }
}
</script>

<template>
  <div class="flex items-center gap-2">
    <Button variant="outline" @click="handleSelect('back')">
      <ArrowLeft class="mr-2 size-4" />
      Back
    </Button>
    <Button v-if="props.invoiceId" variant="outline" @click="handleSelect('download')">
      <Download class="mr-2 size-4" />
      Download PDF
    </Button>
    <Button v-if="props.invoiceId" variant="outline" @click="handleSelect('edit')">
      <FilePenLine class="mr-2 size-4" />
      Edit
    </Button>
    <Button v-if="props.invoiceId" variant="destructive" @click="handleSelect('delete')">
      <Trash2 class="mr-2 size-4" />
      Delete
    </Button>
  </div>

  <div class="border-b border-gray-200" />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent v-if="showComponent && props.invoice" class="sm:max-w-[425px]">
      <InvoiceDelete v-if="showComponent === InvoiceDelete" :invoice="props.invoice" @close="handleSelect('close')" />
    </DialogContent>
  </Dialog>
</template>
