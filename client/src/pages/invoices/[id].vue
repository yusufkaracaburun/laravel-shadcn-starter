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
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { IInvoiceActivity, IInvoiceEmail, IInvoicePayment } from '@/services/invoices.service'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
  if (!invoice.value?.items)
    return []
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
  if (!invoice.value?.payments)
    return []
  return Array.isArray(invoice.value.payments) ? invoice.value.payments : []
})

const invoiceActivities = computed(() => {
  if (!invoice.value?.activities)
    return []
  return Array.isArray(invoice.value.activities) ? invoice.value.activities : []
})

const invoiceEmails = computed(() => {
  if (!invoice.value?.emails)
    return []
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
  if (!dateString)
    return '—'
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      return formatDateForPreview(dateString)
    }
  }
  catch {
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
        <Button class="mt-4" @click="refetch">
          Try Again
        </Button>
      </div>
    </div>

    <div v-else-if="invoice" class="space-y-8">
      <!-- Hero Header Section -->
      <Card class="border-2 bg-linear-to-br from-background to-muted/20">
        <CardContent class="pt-8 pb-6">
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <!-- Left: Invoice Info -->
            <div class="space-y-6 flex-1">
              <div class="flex items-start justify-between gap-6">
                <div class="space-y-2">
                  <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                      <Receipt class="size-5 text-primary" />
                    </div>
                    <div>
                      <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                        Invoice Number
                      </p>
                      <h1 class="text-4xl font-bold tracking-tight mt-1">
                        {{ invoice.invoice_number || `Invoice #${invoice.id}` }}
                      </h1>
                    </div>
                  </div>
                </div>
                <StatusBadge :status="invoice!.status" type="invoice"
                  :icon="statuses.find((s) => s.value === invoice!.status)?.icon"
                  :label="statuses.find((s) => s.value === invoice!.status)?.label" class="text-sm px-3 py-1.5" />
              </div>

              <!-- Dates Grid -->
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 pt-4 border-t">
                <div class="flex items-start gap-3 group">
                  <div class="rounded-lg bg-primary/5 p-2.5 mt-0.5 group-hover:bg-primary/10 transition-colors">
                    <Calendar class="size-4 text-primary" />
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">
                      Issue Date
                    </p>
                    <p class="text-base font-semibold text-foreground">
                      {{ formatDate(invoice.date) }}
                    </p>
                  </div>
                </div>
                <div class="flex items-start gap-3 group">
                  <div class="rounded-lg bg-primary/5 p-2.5 mt-0.5 group-hover:bg-primary/10 transition-colors">
                    <Calendar class="size-4 text-primary" />
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">
                      Due Date
                    </p>
                    <p class="text-base font-semibold text-foreground">
                      {{ formatDate(invoice.date_due) }}
                    </p>
                  </div>
                </div>
                <div class="flex items-start gap-3 group">
                  <div class="rounded-lg bg-primary/5 p-2.5 mt-0.5 group-hover:bg-primary/10 transition-colors">
                    <Clock class="size-4 text-primary" />
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">
                      Payment Terms
                    </p>
                    <p class="text-base font-semibold text-foreground">
                      {{ invoice.due_days }} days
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column: Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Bill To Section -->
          <Card v-if="invoice.customer" class="hover:shadow-md transition-shadow">
            <CardHeader class="pb-4">
              <CardTitle class="text-base font-semibold">
                Bill To
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-5">
                <div>
                  <button class="text-xl font-bold hover:text-primary transition-colors text-left group" @click="
                    router.push({
                      name: '/customers/[id]',
                      params: { id: invoice.customer_id.toString() },
                    })
                    ">
                    {{ invoice.customer.name || `Customer #${invoice.customer_id}` }}
                    <ArrowLeft
                      class="inline-block ml-2 size-4 -rotate-180 opacity-0 group-hover:opacity-100 transition-opacity" />
                  </button>
                  <p v-if="invoice.customer.type" class="text-sm text-muted-foreground mt-1.5">
                    {{ invoice.customer.type }}
                  </p>
                </div>

                <Separator />

                <div v-if="invoice.customer.formatted_address?.length" class="space-y-2">
                  <div v-for="(line, index) in invoice.customer.formatted_address" :key="index"
                    class="flex items-start gap-3 text-sm text-muted-foreground">
                    <MapPin class="size-4 shrink-0 mt-0.5 text-primary/60" />
                    <span class="leading-relaxed">{{ line }}</span>
                  </div>
                </div>
                <div v-else-if="invoice.customer.address" class="flex items-start gap-3 text-sm text-muted-foreground">
                  <MapPin class="size-4 shrink-0 mt-0.5 text-primary/60" />
                  <span class="leading-relaxed">{{ invoice.customer.address }}</span>
                </div>

                <div v-if="invoice.customer.primary_contact?.name"
                  class="flex items-center gap-3 text-sm text-muted-foreground">
                  <User class="size-4 shrink-0 text-primary/60" />
                  <span>{{ invoice.customer.primary_contact.name }}</span>
                </div>

                <div v-if="invoice.customer.email" class="flex items-center gap-3 text-sm text-muted-foreground">
                  <Mail class="size-4 shrink-0 text-primary/60" />
                  <a :href="`mailto:${invoice.customer.email}`"
                    class="hover:text-primary hover:underline transition-colors">
                    {{ invoice.customer.email }}
                  </a>
                </div>

                <div v-if="invoice.customer.phone" class="flex items-center gap-3 text-sm text-muted-foreground">
                  <Phone class="size-4 shrink-0 text-primary/60" />
                  <a :href="`tel:${invoice.customer.phone}`"
                    class="hover:text-primary hover:underline transition-colors">
                    {{ invoice.customer.phone }}
                  </a>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Invoice Items -->
          <Card>
            <CardHeader class="pb-4">
              <CardTitle class="text-base font-semibold">
                Items
              </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div v-if="invoiceItems.length > 0" class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="border-b bg-muted/30">
                      <th class="text-left p-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Description
                      </th>
                      <th class="text-right p-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Qty
                      </th>
                      <th class="text-right p-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Unit Price
                      </th>
                      <th class="text-right p-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        VAT
                      </th>
                      <th class="text-right p-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Total
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in invoiceItems" :key="item.id"
                      class="border-b transition-colors hover:bg-muted/20"
                      :class="index % 2 === 0 ? 'bg-background' : 'bg-muted/5'">
                      <td class="p-5">
                        <div class="font-semibold text-foreground">
                          {{ item.description || '—' }}
                        </div>
                        <div v-if="item.unit" class="mt-1 text-xs text-muted-foreground">
                          Unit: {{ item.unit }}
                        </div>
                      </td>
                      <td class="p-5 text-right">
                        <span class="font-medium text-foreground">
                          {{ formatNumber(item.quantity, 2) }}
                        </span>
                      </td>
                      <td class="p-5 text-right text-muted-foreground">
                        {{ formatCurrency(item.unit_price) }}
                      </td>
                      <td class="p-5 text-right">
                        <div class="flex flex-col items-end gap-0.5">
                          <span class="text-sm font-medium text-foreground">{{ item.vat_rate }}%</span>
                          <span class="text-xs text-muted-foreground">
                            {{ formatCurrency(item.total_vat) }}
                          </span>
                        </div>
                      </td>
                      <td class="p-5 text-right">
                        <span class="text-base font-bold text-foreground">
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
                <p class="text-sm font-medium text-muted-foreground">
                  No items added to this invoice.
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                  Add items to see them listed here.
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Notes Section -->
          <Card v-if="invoice.notes">
            <CardHeader class="pb-4">
              <CardTitle class="text-base font-semibold">
                Additional Notes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-sm text-foreground whitespace-pre-wrap leading-relaxed">
                {{ invoice.notes }}
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Right Column: Summary (Sticky) -->
        <div class="lg:col-span-1">
          <div class="sticky top-4 space-y-6">
            <!-- Financial Summary -->
            <Card class="border-2 shadow-lg">
              <CardHeader class="pb-4">
                <CardTitle class="text-base font-semibold">
                  Financial Summary
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-4">
                  <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-muted-foreground">Subtotal</span>
                    <span class="text-sm font-semibold text-foreground">{{
                      formatCurrency(invoice.subtotal)
                    }}</span>
                  </div>

                  <Separator />

                  <div v-if="
                    invoice.total_vat_0
                    && (typeof invoice.total_vat_0 === 'number' ? invoice.total_vat_0 > 0 : true)
                  " class="flex items-center justify-between py-1.5 text-sm">
                    <span class="text-muted-foreground">VAT 0%</span>
                    <span class="font-medium">{{ formatCurrency(invoice.total_vat_0) }}</span>
                  </div>
                  <div v-if="
                    invoice.total_vat_9
                    && (typeof invoice.total_vat_9 === 'number' ? invoice.total_vat_9 > 0 : true)
                  " class="flex items-center justify-between py-1.5 text-sm">
                    <span class="text-muted-foreground">VAT 9%</span>
                    <span class="font-medium">{{ formatCurrency(invoice.total_vat_9) }}</span>
                  </div>
                  <div v-if="
                    invoice.total_vat_21
                    && (typeof invoice.total_vat_21 === 'number' ? invoice.total_vat_21 > 0 : true)
                  " class="flex items-center justify-between py-1.5 text-sm">
                    <span class="text-muted-foreground">VAT 21%</span>
                    <span class="font-medium">{{ formatCurrency(invoice.total_vat_21) }}</span>
                  </div>

                  <Separator class="my-2" />

                  <div class="flex items-center justify-between pt-2 pb-1 rounded-lg bg-primary/5 px-3 py-3 -mx-3">
                    <span class="text-lg font-bold text-foreground">Total</span>
                    <span class="text-2xl font-bold text-primary">{{
                      formatCurrency(invoice.total)
                    }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Metadata -->
            <Card class="bg-muted/20 border-dashed">
              <CardHeader class="pb-3">
                <CardTitle class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  Metadata
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4 text-xs">
                <div>
                  <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1.5">
                    Invoice ID
                  </p>
                  <p class="font-mono text-sm font-medium text-foreground">
                    #{{ invoice.id }}
                  </p>
                </div>
                <Separator />
                <div>
                  <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1.5">
                    Created
                  </p>
                  <p class="text-xs text-foreground">
                    {{ formatDateTime(invoice.created_at) }}
                  </p>
                </div>
                <div v-if="invoice.updated_at !== invoice.created_at">
                  <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1.5">
                    Updated
                  </p>
                  <p class="text-xs text-foreground">
                    {{ formatDateTime(invoice.updated_at) }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Payments -->
            <InvoicePaymentsSection :payments="invoicePayments" />

            <!-- Activity Log -->
            <InvoiceActivitySection :activities="invoiceActivities" />

            <!-- Sent Emails -->
            <InvoiceEmailsSection :emails="invoiceEmails" />
          </div>
        </div>
      </div>
    </div>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent && invoice" class="sm:max-w-[425px]">
        <InvoiceDelete v-if="showComponent === InvoiceDelete" :invoice="invoice" @close="handleDeleteClose" />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
