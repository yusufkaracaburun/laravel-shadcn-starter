<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import type { ComputedRef } from 'vue'

import { computed } from 'vue'
import { useRoute } from 'vue-router'

import type { IInvoiceActivity, IInvoiceEmail, IInvoicePayment } from '@/services/invoices.service'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import DocumentLayout from '@/layouts/document-layout.vue'
import InvoiceNavbar from '@/pages/invoices/components/invoice-navbar.vue'
import InvoiceSidebar from '@/pages/invoices/components/invoice-sidebar.vue'
import { useGetInvoiceQuery } from '@/services/invoices.service'

const route = useRoute()

const invoiceId = computed(() => Number(route.params.id as string))

const {
  data: invoiceResponse,
  isLoading,
  isError,
  error,
  refetch,
} = useGetInvoiceQuery(invoiceId, {
  include: ['items', 'payments', 'activities', 'emails'],
})

const invoice = computed(() => invoiceResponse.value?.data ?? null) as ComputedRef<
  | (TInvoice & {
      payments?: IInvoicePayment[]
      activities?: IInvoiceActivity[]
      emails?: IInvoiceEmail[]
    })
  | null
>

const pdfUrl = computed(
  () =>
    `/api/invoices/${invoiceId.value}/pdf/preview#toolbar=1&navpanes=0&scrollbar=0&statusbar=0&view=Fit&zoom=100`,
)
</script>

<template>
  <Page
    :title="invoice ? invoice.invoice_number : 'Invoice Details'"
    :description="
      invoice ? `View details for ${invoice.invoice_number}` : 'Loading invoice information...'
    "
  >
    <template #actions>
      <InvoiceNavbar :invoice="invoice" :invoice-id="invoiceId" />
    </template>

    <DocumentLayout>
      <template #main-content>
        <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
          <Loading />
        </div>

        <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
          <div class="text-center">
            <Error
              :code="(error as any)?.response?.status || 500"
              subtitle="Failed to load invoice"
              :error="
                (error as any)?.message ||
                'We couldn\'t load the invoice details. Please try again.'
              "
            />
            <Button class="mt-4 print:hidden" @click="refetch"> Try Again </Button>
          </div>
        </div>

        <div v-else-if="invoice" class="flex flex-1 flex-col items-center justify-center">
          <div class="w-full flex flex-row">
            <div class="flex-1">
              <div v-if="pdfUrl" style="height: calc(100vh - 16px)">
                <iframe :src="pdfUrl" style="width: 100%; height: 100%; border: 0" />
              </div>
            </div>
            <div class="w-1/4 min-h-screen border-l border-gray-200">
              <InvoiceSidebar :invoice="invoice" />
            </div>
          </div>
        </div>
      </template>
    </DocumentLayout>
  </Page>
</template>
