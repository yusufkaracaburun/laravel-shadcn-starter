<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import type { ComputedRef } from 'vue'

import {
  ArrowLeft,
  Clock,
  Download,
  FilePenLine,
  Printer,
  Receipt,
  Trash2,
  User,
} from 'lucide-vue-next'
import { computed, ref, shallowRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { IInvoiceActivity, IInvoiceEmail, IInvoicePayment } from '@/services/invoices.service'

import Error from '@/components/custom-error.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { StatusBadge } from '@/components/ui/status-badge'
import { useGetInvoiceQuery } from '@/services/invoices.service'

import type { TInvoice } from './data/schema'

import InvoiceDelete from './components/invoice-delete.vue'
import { statuses } from './data/data'
import { formatDateForPreview, formatDateTime, formatMoney, formatNumber } from './utils/formatters'

const route = useRoute()
const router = useRouter()

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

// Activity logs for the timeline sidebar
const invoiceActivities = computed(() => {
  if (!invoice.value?.activities) {
    return []
  }
  return Array.isArray(invoice.value.activities) ? invoice.value.activities : []
})

const showComponent = shallowRef<typeof InvoiceDelete | null>(null)
const isDialogOpen = ref(false)

type TCommand = 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      router.push({ name: '/invoices/edit-[id]', params: { id: invoiceId.value.toString() } })
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

function formatDate(dateString: string | null): string {
  if (!dateString) {
    return '—'
  }
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      return formatDateForPreview(dateString)
    }
  } catch {
    // Ignore parsing errors
  }
  return dateString
}

function formatCurrency(value: any): string {
  return formatMoney(value)
}

function printInvoice() {
  window.print()
}

function downloadPDF() {
  // For now, we'll use print functionality
  // In a real implementation, you might use a library like jsPDF or html2pdf
  printInvoice()
}
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- Navigation Bar (Hidden in Print) -->
    <div class="print:hidden bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button variant="outline" @click="router.back()">
            <ArrowLeft class="mr-2 size-4" />
            Back
          </Button>
          <div class="flex items-center gap-2">
            <StatusBadge
              v-if="invoice"
              :status="invoice!.status"
              type="invoice"
              :icon="statuses.find((s) => s.value === invoice!.status)?.icon"
              :label="statuses.find((s) => s.value === invoice!.status)?.label"
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
          <Button v-if="invoice" variant="outline" @click="handleSelect('edit')">
            <FilePenLine class="mr-2 size-4" />
            Edit
          </Button>
          <Button v-if="invoice" variant="destructive" @click="handleSelect('delete')">
            <Trash2 class="mr-2 size-4" />
            Delete
          </Button>
        </div>
      </div>
    </div>

    <!-- Main Content Area: Loading, Error, or Invoice -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <Error
          :code="(error as any)?.response?.status || 500"
          subtitle="Failed to load invoice"
          :error="(error as any)?.message || 'We couldn\'t load the invoice details. Please try again.'
          "
        />
        <Button class="mt-4 print:hidden" @click="refetch">
          Try Again
        </Button>
      </div>
    </div>

    <div v-else-if="invoice" class="max-w-7xl mx-auto flex gap-8 p-8 print:p-0 print:gap-0 print:flex-col lg:flex-row">
      <!-- Main Invoice Content Wrapper -->
      <div class="flex-1 bg-white shadow-sm print:shadow-none print:max-w-none print:mx-0 p-8 print:p-0">
        <!-- Invoice Header -->
        <div class="border-b-2 border-gray-900 pb-8 mb-8">
          <div class="flex justify-between items-start">
            <!-- Company Information -->
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                  <Receipt class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h1 class="text-3xl font-bold text-gray-900">Your Company Name</h1>
                  <p class="text-gray-600">123 Business Street<br>City, State 12345<br>Phone: (555) 123-4567<br>Email:
                    info@company.com</p>
                </div>
              </div>
            </div>

            <!-- Invoice Details -->
            <div class="text-right">
              <h2 class="text-4xl font-bold text-gray-900 mb-2">INVOICE</h2>
              <div class="space-y-2">
                <div>
                  <span class="font-semibold text-gray-700">Invoice #:</span>
                  <span class="ml-2 text-gray-900">{{ invoice.invoice_number || `#${invoice.id}` }}</span>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Date:</span>
                  <span class="ml-2 text-gray-900">{{ formatDate(invoice.date) }}</span>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Due Date:</span>
                  <span class="ml-2 text-gray-900">{{ formatDate(invoice.date_due) }}</span>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Terms:</span>
                  <span class="ml-2 text-gray-900">{{ invoice.due_days }} days</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bill To Section -->
        <div class="mb-8">
          <div class="grid grid-cols-2 gap-8">
            <!-- Bill To -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Bill To:</h3>
              <div class="text-gray-700">
                <p class="font-semibold text-lg mb-1">{{ invoice.customer?.name || `Customer #${invoice.customer_id}` }}
                </p>
                <p v-if="invoice.customer?.type" class="text-sm text-gray-600 mb-2">{{ invoice.customer.type }}</p>

                <div v-if="invoice.customer?.formatted_address?.length" class="text-sm space-y-1">
                  <p v-for="(line, index) in invoice.customer.formatted_address" :key="index">{{ line }}</p>
                </div>
                <p v-else-if="invoice.customer?.address" class="text-sm">{{ invoice.customer.address }}</p>

                <div class="mt-3 space-y-1 text-sm">
                  <p v-if="invoice.customer?.primary_contact?.name">
                    <span class="font-medium">Contact:</span> {{ invoice.customer.primary_contact.name }}
                  </p>
                  <p v-if="invoice.customer?.email">
                    <span class="font-medium">Email:</span> {{ invoice.customer.email }}
                  </p>
                  <p v-if="invoice.customer?.phone">
                    <span class="font-medium">Phone:</span> {{ invoice.customer.phone }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Ship To (if different) -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-3">Ship To:</h3>
              <div class="text-gray-700">
                <p class="text-sm">Same as billing address</p>
                <!-- You can add shipping address logic here if needed -->
              </div>
            </div>
          </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="mb-8">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-50">
                <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-900">Description</th>
                <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">Qty</th>
                <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">Unit Price</th>
                <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">VAT Rate</th>
                <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">VAT Amount</th>
                <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in invoiceItems" :key="item.id"
                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                <td class="border border-gray-300 px-4 py-3">
                  <div class="font-medium text-gray-900">{{ item.description || '—' }}</div>
                  <div v-if="item.unit" class="text-sm text-gray-600 mt-1">{{ item.unit }}</div>
                </td>
                <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">{{ formatNumber(item.quantity, 2)
                  }}</td>
                <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">{{ formatCurrency(item.unit_price)
                  }}</td>
                <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">{{ item.vat_rate }}%</td>
                <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">{{ formatCurrency(item.total_vat)
                  }}</td>
                <td class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">{{
                  formatCurrency(item.total_incl_vat) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals Section -->
        <div class="flex justify-end mb-8">
          <div class="w-80">
            <div class="border-t border-gray-900 pt-4 space-y-3">
              <div class="flex justify-between text-gray-700">
                <span>Subtotal:</span>
                <span>{{ formatCurrency(invoice.subtotal) }}</span>
              </div>

              <div
                v-if="invoice?.total_vat_0 && (typeof invoice.total_vat_0 === 'number' ? invoice.total_vat_0 > 0 : true)"
                class="flex justify-between text-gray-700">
                <span>VAT 0%:</span>
                <span>{{ formatCurrency(invoice.total_vat_0) }}</span>
              </div>
              <div
                v-if="invoice?.total_vat_9 && (typeof invoice.total_vat_9 === 'number' ? invoice.total_vat_9 > 0 : true)"
                class="flex justify-between text-gray-700">
                <span>VAT 9%:</span>
                <span>{{ formatCurrency(invoice.total_vat_9) }}</span>
              </div>
              <div
                v-if="invoice?.total_vat_21 && (typeof invoice.total_vat_21 === 'number' ? invoice.total_vat_21 > 0 : true)"
                class="flex justify-between text-gray-700">
                <span>VAT 21%:</span>
                <span>{{ formatCurrency(invoice.total_vat_21) }}</span>
              </div>

              <div class="border-t-2 border-gray-900 pt-3 flex justify-between text-xl font-bold text-gray-900">
                <span>Total:</span>
                <span>{{ formatCurrency(invoice.total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="invoice.notes" class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes:</h3>
          <div class="bg-gray-50 p-4 rounded border text-gray-700 whitespace-pre-wrap">
            {{ invoice.notes }}
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-300 pt-6 text-center text-sm text-gray-600">
          <p>Thank you for your business! Payment is due within {{ invoice.due_days }} days.</p>
          <p class="mt-2">Please make checks payable to "Your Company Name" and include the invoice number on your
            payment.          </p>
        </div>
      </div>

      <!-- Activity Timeline Sidebar -->
      <div class="w-[320px] shrink-0 print:hidden">
        <div class="bg-gray-50 rounded-lg p-6 sticky top-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
            <Clock class="w-5 h-5 text-gray-600" />
            Activity Timeline
          </h3>

          <div v-if="invoiceActivities.length === 0" class="text-center py-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3">
              <Clock class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm text-gray-500">No activity yet</p>
          </div>

          <div v-else class="space-y-4 max-h-96 overflow-y-auto">
            <div v-for="(activity, index) in invoiceActivities" :key="activity.id"
              class="relative flex gap-4 pb-4 last:pb-0">
              <!-- Timeline line -->
              <div class="flex flex-col items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-sm"></div>
                <div v-if="index < invoiceActivities.length - 1" class="w-0.5 h-full bg-gray-200 mt-2"></div>
              </div>

              <!-- Activity content -->
              <div class="flex-1 min-w-0 pb-4">
                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
                  <p class="text-sm text-gray-900 font-medium mb-1">{{ activity.description }}</p>

                  <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                    <User class="w-3 h-3" />
                    <span>{{ activity.causer?.name || 'System' }}</span>
                    <span class="text-gray-300">•</span>
                    <span>{{ formatDateTime(activity.created_at) }}</span>
                  </div>

                  <!-- Event badge -->
                  <div v-if="activity.event"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ activity.event }}
                  </div>

                  <!-- Show properties changes if available -->
                  <div v-if="activity.properties && Object.keys(activity.properties.attributes || {}).length > 0"
                    class="mt-2 pt-2 border-t border-gray-100">
                    <details class="text-xs">
                      <summary class="cursor-pointer text-gray-600 hover:text-gray-800 font-medium">
                        View changes
                      </summary>
                      <div class="mt-2 space-y-1">
                        <div v-for="(value, key) in activity.properties.attributes" :key="key" class="text-gray-700">
                          <span class="font-medium">{{ key.replace(/_/g, ' ') }}:</span>
                          <span class="ml-1">{{ typeof value === 'object' ? JSON.stringify(value) : value }}</span>
                        </div>
                      </div>
                    </details>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="invoiceActivities.length > 0" class="mt-4 pt-4 border-t border-gray-200">
              <p class="text-xs text-gray-500 text-center">
                {{ invoiceActivities.length }} activit{{ invoiceActivities.length === 1 ? 'y' : 'ies' }} logged
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Dialog v-model:open="isDialogOpen" class="print:hidden">
      <DialogContent v-if="showComponent && invoice" class="sm:max-w-[425px]">
        <InvoiceDelete v-if="showComponent === InvoiceDelete" :invoice="invoice" @close="handleDeleteClose" />
      </DialogContent>
    </Dialog>
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

