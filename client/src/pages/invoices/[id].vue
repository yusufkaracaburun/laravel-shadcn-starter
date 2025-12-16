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
import InvoiceNavbar from '@/pages/invoices/components/invoice-navbar.vue'
import Loading from '@/components/loading.vue'
import InvoicePreview from '@/pages/invoices/components/invoice-preview.vue'
import { Button } from '@/components/ui/button'
import InvoiceSidebar from '@/pages/invoices/components/invoice-sidebar.vue'
import { useGetInvoiceQuery } from '@/services/invoices.service'

const route = useRoute()

const invoiceId = computed(() => {
  const params = route.params as { id?: string | string[] }
  const id = params.id
  if (typeof id === 'string') {
    return Number(id)
  }
  if (Array.isArray(id) && id.length > 0) {
    return Number(id[0])
  }
  return 0
})

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

// Normalize items structure - backend returns items as { data: [...] } but we expect an array
const invoiceItems = computed(() => {
  if (!invoice.value?.items) {
    return []
  }
  // Handle paginated structure: items: { data: [...] }
  if (
    typeof invoice.value.items === 'object'
    && 'data' in invoice.value.items
    && Array.isArray(invoice.value.items.data)
  ) {
    return invoice.value.items.data
  }
  // Handle direct array structure: items: [...]
  if (Array.isArray(invoice.value.items)) {
    return invoice.value.items
  }
  return []
})
</script>

<template>
  <div class="min-h-screen">
    <InvoiceNavbar :invoice="invoice" :invoice-id="invoiceId" />
    <!-- Main Content Area: Loading, Error, or Invoice -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <Error :code="(error as any)?.response?.status || 500" subtitle="Failed to load invoice" :error="(error as any)?.message || 'We couldn\'t load the invoice details. Please try again.'
          " />
        <Button class="mt-4 print:hidden" @click="refetch">
          Try Again
        </Button>
      </div>
    </div>

    <div v-else-if="invoice" class="w-full min-h-screen">
      <div class="flex flex-row gap-4">
        <div class="flex-1 m-4">
          <InvoicePreview :invoice="invoice" :invoice-items="invoiceItems" />
        </div>
        <div class="w-1/4 min-h-screen">
          <InvoiceSidebar :invoice="invoice" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  @page {
    margin: 0.5in;
    size: letter;
  }

  body {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .print\\:hidden {
    display: none !important;
  }

  .print\\:shadow-none {
    box-shadow: none !important;
  }

  .print\\:max-w-none {
    max-width: none !important;
  }

  .print\\:mx-0 {
    margin-left: 0 !important;
    margin-right: 0 !important;
  }

  .print\\:p-0 {
    padding: 0 !important;
  }
}

/* Ensure proper table printing */
table {
  page-break-inside: avoid;
}

tr {
  page-break-inside: avoid;
  page-break-after: auto;
}

thead {
  display: table-header-group;
}

tbody {
  display: table-row-group;
}
</style>
