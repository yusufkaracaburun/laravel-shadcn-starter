<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import {
  ArrowLeft,
  FileText,
  FilePenLine,
  Trash2,
  User,
  Calendar,
  DollarSign,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { Invoice } from './data/schema'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { StatusBadge } from '@/components/ui/status-badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { useGetInvoiceQuery } from '@/services/invoices.service'

import { statuses } from './data/data'
import InvoiceDelete from './components/invoice-delete.vue'

const route = useRoute()
const router = useRouter()

const invoiceId = computed(() => Number(route.params.id))

const { data: invoiceResponse, isLoading, isError, error, refetch } = useGetInvoiceQuery(invoiceId, {
  include: ['items'],
})

const invoice = computed<Invoice | null>(() => invoiceResponse.value?.data ?? null)

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

// Format date from "d-m-Y H:i:s" format
function formatDateTime(dateString: string | null): string {
  if (!dateString) return '—'
  try {
    // Try parsing as "d-m-Y H:i:s" format first
    if (dateString.includes('-') && dateString.includes(' ')) {
      const [datePart, timePart] = dateString.split(' ')
      const [day, month, year] = datePart.split('-')
      if (day && month && year && year.length === 4) {
        const date = new Date(`${year}-${month}-${day} ${timePart}`)
        return date.toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        })
      }
    }
    // Try parsing as ISO format
    const date = new Date(dateString)
    if (!isNaN(date.getTime())) {
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }
  } catch (error) {
    // Ignore parsing errors
  }
  return dateString
}

function formatDate(dateString: string | null): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }
  } catch (error) {
    // Ignore parsing errors
  }
  return dateString
}

function formatMoney(value: any): string {
  if (typeof value === 'object' && value !== null && 'formatted' in value) {
    return value.formatted
  }
  if (typeof value === 'number') {
    return new Intl.NumberFormat('nl-NL', {
      style: 'currency',
      currency: 'EUR',
    }).format(value)
  }
  return '€0.00'
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

    <Error
      v-else-if="isError"
      :error="error"
      :retry="refetch"
      title="Failed to load invoice"
      description="We couldn't load the invoice details. Please try again."
    />

    <div v-else-if="invoice" class="space-y-6">
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <FileText class="size-5 text-muted-foreground" />
              <CardTitle>{{ invoice.invoice_number || `Invoice #${invoice.id}` }}</CardTitle>
            </div>
            <StatusBadge
              :status="invoice.status"
              type="invoice"
              :icon="statuses.find((s) => s.value === invoice.status)?.icon"
              :label="statuses.find((s) => s.value === invoice.status)?.label"
            />
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div v-if="invoice.customer" class="flex items-center gap-2">
              <User class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Customer</p>
                <button
                  class="text-sm hover:underline cursor-pointer"
                  @click="
                    router.push({
                      name: '/customers/[id]',
                      params: { id: invoice.customer_id.toString() },
                    })
                  "
                >
                  {{ invoice.customer.name || `Customer #${invoice.customer_id}` }}
                </button>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <Calendar class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Date</p>
                <p class="text-sm">{{ formatDate(invoice.date) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <Calendar class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Due Date</p>
                <p class="text-sm">{{ formatDate(invoice.date_due) }}</p>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Due Days</p>
              <p class="text-sm font-semibold">{{ invoice.due_days }} days</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Invoice ID</p>
              <p class="text-sm font-semibold">#{{ invoice.id }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <DollarSign class="size-5 text-muted-foreground" />
            <CardTitle>Financial Breakdown</CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-muted-foreground">Subtotal</p>
              <p class="text-sm font-semibold">{{ formatMoney(invoice.subtotal) }}</p>
            </div>
            <div
              v-if="
                invoice.total_vat_0 &&
                (typeof invoice.total_vat_0 === 'number' ? invoice.total_vat_0 > 0 : true)
              "
              class="flex items-center justify-between"
            >
              <p class="text-sm font-medium text-muted-foreground">VAT 0%</p>
              <p class="text-sm">{{ formatMoney(invoice.total_vat_0) }}</p>
            </div>
            <div
              v-if="
                invoice.total_vat_9 &&
                (typeof invoice.total_vat_9 === 'number' ? invoice.total_vat_9 > 0 : true)
              "
              class="flex items-center justify-between"
            >
              <p class="text-sm font-medium text-muted-foreground">VAT 9%</p>
              <p class="text-sm">{{ formatMoney(invoice.total_vat_9) }}</p>
            </div>
            <div
              v-if="
                invoice.total_vat_21 &&
                (typeof invoice.total_vat_21 === 'number' ? invoice.total_vat_21 > 0 : true)
              "
              class="flex items-center justify-between"
            >
              <p class="text-sm font-medium text-muted-foreground">VAT 21%</p>
              <p class="text-sm">{{ formatMoney(invoice.total_vat_21) }}</p>
            </div>
            <div class="flex items-center justify-between border-t pt-4">
              <p class="text-base font-semibold">Total</p>
              <p class="text-base font-bold">{{ formatMoney(invoice.total) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Invoice Items -->
      <Card v-if="invoice.items && invoice.items.length > 0">
        <CardHeader>
          <CardTitle>Invoice Items</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b">
                  <th class="text-left p-2 font-medium text-muted-foreground">Description</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Quantity</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Unit Price</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">VAT Rate</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Excl. VAT</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">VAT</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Incl. VAT</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in invoice.items"
                  :key="item.id"
                  class="border-b hover:bg-muted/50"
                >
                  <td class="p-2 font-medium">{{ item.description || '—' }}</td>
                  <td class="p-2 text-right">{{ item.quantity }}</td>
                  <td class="p-2 text-right">{{ formatMoney(item.unit_price) }}</td>
                  <td class="p-2 text-right">{{ item.vat_rate }}%</td>
                  <td class="p-2 text-right">{{ formatMoney(item.total_excl_vat) }}</td>
                  <td class="p-2 text-right">{{ formatMoney(item.total_vat) }}</td>
                  <td class="p-2 text-right font-semibold">{{ formatMoney(item.total_incl_vat) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <Card v-if="invoice.notes">
        <CardHeader>
          <CardTitle>Notes</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-sm whitespace-pre-wrap">{{ invoice.notes }}</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Timestamps</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Created At</p>
              <p class="text-sm">{{ formatDateTime(invoice.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Updated At</p>
              <p class="text-sm">{{ formatDateTime(invoice.updated_at) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent && invoice" class="sm:max-w-[425px]">
        <InvoiceDelete
          v-if="showComponent === InvoiceDelete"
          :invoice="invoice"
          @close="handleDeleteClose"
        />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
