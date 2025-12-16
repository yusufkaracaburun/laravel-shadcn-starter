<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import type { ComputedRef } from 'vue'

import {
  ArrowLeft,
  Calendar,
  Clock,
  FilePenLine,
  Mail,
  MapPin,
  Phone,
  Receipt,
  Trash2,
  User,
} from 'lucide-vue-next'
import { computed, ref, shallowRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { IInvoiceActivity, IInvoiceEmail, IInvoicePayment } from '@/services/invoices.service'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Separator } from '@/components/ui/separator'
import { StatusBadge } from '@/components/ui/status-badge'
import { useGetInvoiceQuery } from '@/services/invoices.service'

import type { TInvoice } from './data/schema'

import InvoiceActivitySection from './components/invoice-activity-section.vue'
import InvoiceDelete from './components/invoice-delete.vue'
import InvoiceEmailsSection from './components/invoice-emails-section.vue'
import InvoicePaymentsSection from './components/invoice-payments-section.vue'
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

// Normalize payments, activities, and emails arrays
const invoicePayments = computed(() => {
  if (!invoice.value?.payments) {
    return []
  }
  return Array.isArray(invoice.value.payments) ? invoice.value.payments : []
})

const invoiceActivities = computed(() => {
  if (!invoice.value?.activities) {
    return []
  }
  return Array.isArray(invoice.value.activities) ? invoice.value.activities : []
})

const invoiceEmails = computed(() => {
  if (!invoice.value?.emails) {
    return []
  }
  return Array.isArray(invoice.value.emails) ? invoice.value.emails : []
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
</script>

<template>
  <Page title="Invoice Details" description="View and manage invoice information" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="router.back()">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <Button v-if="invoice" variant="outline" size="sm" @click="handleSelect('edit')">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button v-if="invoice" variant="destructive" size="sm" @click="handleSelect('delete')">
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </template>

    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <Error :code="(error as any)?.response?.status || 500" subtitle="Failed to load invoice" :error="(error as any)?.message || 'We couldn\'t load the invoice details. Please try again.'
          " />
        <Button class="mt-4" @click="refetch">Try Again</Button>
      </div>
    </div>

    <div v-else-if="invoice" class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
              <Receipt class="size-5 text-primary" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Invoice</p>
              <h1 class="text-3xl font-bold tracking-tight">
                {{ invoice.invoice_number || `#${invoice.id}` }}
              </h1>
            </div>
          </div>
        </div>
        <div v-if="invoice" class="flex items-center gap-2">
          <StatusBadge :status="invoice!.status" type="invoice"
            :icon="statuses.find((s) => s.value === invoice!.status)?.icon"
            :label="statuses.find((s) => s.value === invoice!.status)?.label" class="text-sm" />
        </div>
      </div>

      <!-- Key Information Cards -->
      <div class="grid gap-4 sm:grid-cols-3">
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="flex size-10 items-center justify-center rounded-lg bg-blue-500/10">
                <Calendar class="size-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-muted-foreground mb-1">Issue Date</p>
                <p class="text-sm font-semibold text-foreground truncate">{{ formatDate(invoice.date) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="flex size-10 items-center justify-center rounded-lg bg-orange-500/10">
                <Calendar class="size-5 text-orange-600 dark:text-orange-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-muted-foreground mb-1">Due Date</p>
                <p class="text-sm font-semibold text-foreground truncate">{{ formatDate(invoice.date_due) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="flex size-10 items-center justify-center rounded-lg bg-green-500/10">
                <Clock class="size-5 text-green-600 dark:text-green-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-muted-foreground mb-1">Payment Terms</p>
                <p class="text-sm font-semibold text-foreground truncate">{{ invoice.due_days }} days</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column: Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Customer Information -->
          <Card v-if="invoice.customer">
            <CardHeader>
              <CardTitle class="text-lg font-semibold"> Bill To </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div>
                  <button
                    class="text-lg font-semibold hover:text-primary transition-colors text-left group flex items-center gap-2"
                    @click="
                      router.push({
                        name: '/customers/[id]',
                        params: { id: invoice.customer_id.toString() },
                      })
                      ">
                    {{ invoice.customer.name || `Customer #${invoice.customer_id}` }}
                    <ArrowLeft class="size-4 -rotate-180 opacity-0 group-hover:opacity-100 transition-opacity" />
                  </button>
                  <p v-if="invoice.customer.type" class="text-sm text-muted-foreground mt-1">
                    {{ invoice.customer.type }}
                  </p>
                </div>

                <Separator />

                <div class="grid gap-4 sm:grid-cols-2">
                  <div v-if="invoice.customer.formatted_address?.length || invoice.customer.address" class="space-y-2">
                    <div class="flex items-start gap-2 text-sm">
                      <MapPin class="size-4 shrink-0 mt-0.5 text-muted-foreground" />
                      <div class="space-y-1">
                        <p v-if="invoice.customer.formatted_address?.length" class="text-muted-foreground">
                          <span v-for="(line, index) in invoice.customer.formatted_address" :key="index" class="block">
                            {{ line }}
                          </span>
                        </p>
                        <p v-else-if="invoice.customer.address" class="text-muted-foreground">
                          {{ invoice.customer.address }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div v-if="invoice.customer.primary_contact?.name" class="flex items-center gap-2 text-sm">
                      <User class="size-4 shrink-0 text-muted-foreground" />
                      <span class="text-muted-foreground">{{ invoice.customer.primary_contact.name }}</span>
                    </div>

                    <div v-if="invoice.customer.email" class="flex items-center gap-2 text-sm">
                      <Mail class="size-4 shrink-0 text-muted-foreground" />
                      <a :href="`mailto:${invoice.customer.email}`"
                        class="text-muted-foreground hover:text-primary hover:underline transition-colors">
                        {{ invoice.customer.email }}
                      </a>
                    </div>

                    <div v-if="invoice.customer.phone" class="flex items-center gap-2 text-sm">
                      <Phone class="size-4 shrink-0 text-muted-foreground" />
                      <a :href="`tel:${invoice.customer.phone}`"
                        class="text-muted-foreground hover:text-primary hover:underline transition-colors">
                        {{ invoice.customer.phone }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Invoice Items -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg font-semibold"> Invoice Items </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div v-if="invoiceItems.length > 0" class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="border-b bg-muted/50">
                      <th class="text-left p-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Description
                      </th>
                      <th class="text-right p-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Qty
                      </th>
                      <th class="text-right p-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Unit Price
                      </th>
                      <th class="text-right p-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        VAT
                      </th>
                      <th class="text-right p-4 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Total
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in invoiceItems" :key="item.id"
                      class="border-b transition-colors hover:bg-muted/30"
                      :class="index % 2 === 0 ? 'bg-background' : 'bg-muted/20'">
                      <td class="p-4">
                        <div class="font-medium text-foreground">
                          {{ item.description || '—' }}
                        </div>
                        <div v-if="item.unit" class="mt-1 text-xs text-muted-foreground">
                          {{ item.unit }}
                        </div>
                      </td>
                      <td class="p-4 text-right">
                        <span class="text-sm font-medium text-foreground">
                          {{ formatNumber(item.quantity, 2) }}
                        </span>
                      </td>
                      <td class="p-4 text-right">
                        <span class="text-sm text-muted-foreground">
                          {{ formatCurrency(item.unit_price) }}
                        </span>
                      </td>
                      <td class="p-4 text-right">
                        <div class="flex flex-col items-end gap-0.5">
                          <span class="text-sm font-medium text-foreground">{{ item.vat_rate }}%</span>
                          <span class="text-xs text-muted-foreground">
                            {{ formatCurrency(item.total_vat) }}
                          </span>
                        </div>
                      </td>
                      <td class="p-4 text-right">
                        <span class="text-sm font-semibold text-foreground">
                          {{ formatCurrency(item.total_incl_vat) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="p-12 text-center">
                <div class="inline-flex items-center justify-center size-12 rounded-full bg-muted mb-4">
                  <Receipt class="size-6 text-muted-foreground" />
                </div>
                <p class="text-sm font-medium text-muted-foreground">No items added to this invoice.</p>
                <p class="text-xs text-muted-foreground mt-1">Add items to see them listed here.</p>
              </div>
            </CardContent>
          </Card>

          <!-- Notes Section -->
          <Card v-if="invoice.notes">
            <CardHeader>
              <CardTitle class="text-lg font-semibold"> Additional Notes </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-sm text-muted-foreground whitespace-pre-wrap leading-relaxed">
                {{ invoice.notes }}
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Right Column: Summary (Sticky) -->
        <div class="lg:col-span-1">
          <div class="sticky top-4 space-y-6">
            <!-- Financial Summary -->
            <Card>
              <CardHeader>
                <CardTitle class="text-lg font-semibold"> Financial Summary </CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-muted-foreground">Subtotal</span>
                  <span class="text-sm font-medium text-foreground">
                    {{ formatCurrency(invoice.subtotal) }}
                  </span>
                </div>

                <Separator />

                <div v-if="
                  invoice?.total_vat_0 &&
                  (typeof invoice.total_vat_0 === 'number' ? invoice.total_vat_0 > 0 : true)
                " class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">VAT 0%</span>
                  <span class="font-medium text-foreground">{{
                    formatCurrency(invoice.total_vat_0)
                  }}</span>
                </div>
                <div v-if="
                  invoice?.total_vat_9 &&
                  (typeof invoice.total_vat_9 === 'number' ? invoice.total_vat_9 > 0 : true)
                " class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">VAT 9%</span>
                  <span class="font-medium text-foreground">{{
                    formatCurrency(invoice.total_vat_9)
                  }}</span>
                </div>
                <div v-if="
                  invoice?.total_vat_21 &&
                  (typeof invoice.total_vat_21 === 'number' ? invoice.total_vat_21 > 0 : true)
                " class="flex items-center justify-between text-sm">
                  <span class="text-muted-foreground">VAT 21%</span>
                  <span class="font-medium text-foreground">{{
                    formatCurrency(invoice.total_vat_21)
                  }}</span>
                </div>

                <Separator />

                <div class="flex items-center justify-between rounded-lg bg-primary/5 p-3 -mx-3">
                  <span class="text-base font-semibold text-foreground">Total</span>
                  <span class="text-xl font-bold text-primary">
                    {{ formatCurrency(invoice.total) }}
                  </span>
                </div>
              </CardContent>
            </Card>

            <!-- Metadata -->
            <Card class="bg-muted/30">
              <CardHeader>
                <CardTitle class="text-sm font-semibold text-muted-foreground">
                  Metadata
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-3 text-sm">
                <div>
                  <p class="text-xs font-medium text-muted-foreground mb-1">Invoice ID</p>
                  <p class="font-mono text-sm font-medium text-foreground">#{{ invoice.id }}</p>
                </div>
                <Separator />
                <div>
                  <p class="text-xs font-medium text-muted-foreground mb-1">Created</p>
                  <p class="text-sm text-foreground">
                    {{ formatDateTime(invoice.created_at) }}
                  </p>
                </div>
                <div v-if="invoice.updated_at !== invoice.created_at">
                  <p class="text-xs font-medium text-muted-foreground mb-1">Updated</p>
                  <p class="text-sm text-foreground">
                    {{ formatDateTime(invoice.updated_at) }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <Accordion type="multiple" class="w-full space-y-6">
              <!-- Payments -->
              <AccordionItem value="payments" class="border-none">
                <AccordionTrigger class="hover:no-underline text-lg font-semibold text-foreground py-0">
                  Payments
                </AccordionTrigger>
                <AccordionContent>
                  <InvoicePaymentsSection :payments="invoicePayments" />
                </AccordionContent>
              </AccordionItem>

              <!-- Activity Log -->
              <AccordionItem value="activity-log" class="border-none">
                <AccordionTrigger class="hover:no-underline text-lg font-semibold text-foreground py-0">
                  Activity Log
                </AccordionTrigger>
                <AccordionContent>
                  <InvoiceActivitySection :activities="invoiceActivities" />
                </AccordionContent>
              </AccordionItem>

              <!-- Sent Emails -->
              <AccordionItem value="sent-emails" class="border-none">
                <AccordionTrigger class="hover:no-underline text-lg font-semibold text-foreground py-0">
                  Sent Emails
                </AccordionTrigger>
                <AccordionContent>
                  <InvoiceEmailsSection :emails="invoiceEmails" />
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </div>
        </div>
      </div>
    </div>

    <Dialog v-model:open="isDialogOpen">
      <DialogContent v-if="showComponent && invoice" class="sm:max-w-[425px]">
        <InvoiceDelete v-if="showComponent === InvoiceDelete" :invoice="invoice" @close="handleDeleteClose" />
      </DialogContent>
    </Dialog>
  </Page>
</template>
