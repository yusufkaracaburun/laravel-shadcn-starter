<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import type { ComputedRef } from 'vue'

import { computed, ref } from 'vue'

import type { IInvoice } from '@/pages/invoices/models/invoice'

import Page from '@/components/global-layout/basic-page.vue'
import { useInvoices } from '@/composables/use-invoices'
import DocumentLayout from '@/layouts/document-layout.vue'
import InvoiceNavbar from '@/pages/invoices/view/components/invoice-navbar.vue'
import InvoiceSidebar from '@/pages/invoices/view/components/invoice-sidebar.vue'

const {
  invoiceId,
  invoiceByIdResponse: invoiceResponse,
  isLoadingInvoiceById: isLoading,
  isErrorInvoiceById: isError,
  errorInvoiceById: error,
  fetchInvoiceByIdData,
} = useInvoices()

const invoice = computed(
  () => invoiceResponse.value?.data as IInvoice,
) as ComputedRef<IInvoice>

const pageTitle = ref('Invoice Details')
const pageDescription = ref('Loading invoice information...')

const pdfUrl = computed(() =>
  invoiceId.value
    ? `/api/invoices/${invoiceId.value}/pdf/preview#toolbar=1&navpanes=0&scrollbar=0&view=Fit`
    : undefined,
)

const pdfStyle = computed(() => {
  return {
    width: '100%',
    height: '100%',
    border: '0',
  }
})

const pdfHeight = computed(() => {
  return {
    height: 'calc(100vh - 16px)',
  }
})
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <InvoiceNavbar
        v-if="invoice"
        :invoice="invoice"
        :invoice-id="invoiceId ?? 0"
        @update:title="pageTitle = $event"
        @update:description="pageDescription = $event"
      />
    </template>

    <DocumentLayout
      :is-loading="isLoading"
      :is-error="isError"
      :error-object="error"
      :on-retry="fetchInvoiceByIdData"
    >
      <div class="flex flex-1 flex-col items-center justify-center">
        <div class="w-full flex flex-row">
          <div class="flex-1">
            <div v-if="pdfUrl" :style="pdfHeight">
              <iframe :src="pdfUrl" :style="pdfStyle" />
            </div>
          </div>
          <div class="w-1/4 min-h-screen border-l border-gray-200">
            <InvoiceSidebar :invoice="invoice" />
          </div>
        </div>
      </div>
    </DocumentLayout>
  </Page>
</template>
