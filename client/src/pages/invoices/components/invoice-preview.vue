<script setup lang="ts">
import type { ComputedRef } from 'vue'
import { computed } from 'vue'

import { Receipt } from 'lucide-vue-next'

import type { TInvoice } from '@/pages/invoices/data/schema'
import {
  formatDateForPreview,
  formatDateTime,
  formatMoney,
  formatNumber,
} from '@/pages/invoices/utils/formatters'
import type { IInvoiceItem } from '@/services/invoices.service'
import { statuses } from '../../../../.tmp/shadcn-vue-admin/src/pages/tasks/data/data';

const props = defineProps<{
  formValues: TInvoice | null
  items: IInvoiceItem[]
}>()

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
  <div
    class="bg-white shadow-sm border border-gray-200 print:shadow-none print:max-w-none print:mx-0 p-8 print:p-0 m-8">
    <!-- Invoice Header -->
    <div class="border-b-2 border-gray-200 pb-8 mb-8">
      <div class="flex justify-between items-start">
        <!-- Company Information -->
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
              <Receipt class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Your Company Name</h1>
              <p class="text-gray-600">
                123 Business Street<br />City, State 12345<br />Phone: (555) 123-4567<br />Email:
                info@company.com
              </p>
            </div>
          </div>
        </div>

        <!-- Invoice Details -->
        <div class="text-right">
          <h2 class="text-4xl font-bold text-gray-900 mb-2">INVOICE</h2>
          <div class="space-y-2">
            <div>
              <span class="font-semibold text-gray-700">Invoice #:</span>
              <span class="ml-2 text-gray-900">{{
                props.formValues?.invoice_number ||
                (props.formValues?.id ? `#${props.formValues.id}` : '—')
                }}</span>
            </div>
            <div>
              <span class="font-semibold text-gray-700">Date:</span>
              <span class="ml-2 text-gray-900">{{
                formatDate(props.formValues?.date || null)
                }}</span>
            </div>
            <div>
              <span class="font-semibold text-gray-700">Due Date:</span>
              <span class="ml-2 text-gray-900">{{
                formatDate(props.formValues?.date_due || null)
                }}</span>
            </div>
            <div>
              <span class="font-semibold text-gray-700">Terms:</span>
              <span class="ml-2 text-gray-900">{{ props.formValues?.due_days || '—' }} days</span>
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
            <p class="font-semibold text-lg mb-1">
              {{
                props.formValues?.customer?.name ||
                (props.formValues?.customer_id ? `Customer #${props.formValues.customer_id}` : '—')
              }}
            </p>
            <p v-if="props.formValues?.customer?.type" class="text-sm text-gray-600 mb-2">
              {{ props.formValues.customer.type }}
            </p>

            <div v-if="props.formValues?.customer?.formatted_address?.length" class="text-sm space-y-1">
              <p v-for="(line, index) in props.formValues.customer.formatted_address" :key="index">
                {{ line }}
              </p>
            </div>
            <p v-else-if="props.formValues?.customer?.address" class="text-sm">
              {{ props.formValues.customer.address }}
            </p>

            <div class="mt-3 space-y-1 text-sm">
              <p v-if="props.formValues?.customer?.primary_contact?.name">
                <span class="font-medium">Contact:</span>
                {{ props.formValues.customer.primary_contact.name }}
              </p>
              <p v-if="props.formValues?.customer?.email">
                <span class="font-medium">Email:</span> {{ props.formValues.customer.email }}
              </p>
              <p v-if="props.formValues?.customer?.phone">
                <span class="font-medium">Phone:</span> {{ props.formValues.customer.phone }}
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
            <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-900">
              Name
            </th>
            <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              Qty
            </th>
            <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              Unit Price
            </th>
            <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              VAT Rate
            </th>
            <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              VAT Amount
            </th>
            <th class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              Total
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoiceItems" :key="item.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
            <td class="border border-gray-300 px-4 py-3">
              <div class="font-medium text-gray-900">
                {{ item.description || '—' }}
              </div>
              <div v-if="item.unit" class="text-sm text-gray-600 mt-1">
                {{ item.unit }}
              </div>
            </td>
            <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">
              {{ formatNumber(item.quantity, 2) }}
            </td>
            <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">
              {{ formatCurrency(item.unit_price) }}
            </td>
            <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">
              {{ item.vat_rate }}%
            </td>
            <td class="border border-gray-300 px-4 py-3 text-right text-gray-900">
              {{ formatCurrency(item.total_vat) }}
            </td>
            <td class="border border-gray-300 px-4 py-3 text-right font-semibold text-gray-900">
              {{ formatCurrency(item.total_incl_vat) }}
            </td>
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
            <span>{{ formatCurrency(props.formValues?.subtotal) }}</span>
          </div>

          <div v-if="
            props.formValues?.total_vat_0 &&
            (typeof props.formValues.total_vat_0 === 'object'
              ? Number.parseFloat(props.formValues.total_vat_0.amount) > 0
              : typeof props.formValues.total_vat_0 === 'number'
                ? props.formValues.total_vat_0 > 0
                : false)
          " class="flex justify-between text-gray-700">
            <span>VAT 0%:</span>
            <span>{{ formatCurrency(props.formValues.total_vat_0) }}</span>
          </div>
          <div v-if="
            props.formValues?.total_vat_9 &&
            (typeof props.formValues.total_vat_9 === 'object'
              ? Number.parseFloat(props.formValues.total_vat_9.amount) > 0
              : typeof props.formValues.total_vat_9 === 'number'
                ? props.formValues.total_vat_9 > 0
                : false)
          " class="flex justify-between text-gray-700">
            <span>VAT 9%:</span>
            <span>{{ formatCurrency(props.formValues.total_vat_9) }}</span>
          </div>
          <div v-if="
            props.formValues?.total_vat_21 &&
            (typeof props.formValues.total_vat_21 === 'object'
              ? Number.parseFloat(props.formValues.total_vat_21.amount) > 0
              : typeof props.formValues.total_vat_21 === 'number'
                ? props.formValues.total_vat_21 > 0
                : false)
          " class="flex justify-between text-gray-700">
            <span>VAT 21%:</span>
            <span>{{ formatCurrency(props.formValues.total_vat_21) }}</span>
          </div>

          <div class="border-t-2 border-gray-900 pt-3 flex justify-between text-xl font-bold text-gray-900">
            <span>Total:</span>
            <span>{{ formatCurrency(props.formValues?.total) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes Section -->
    <div v-if="props.formValues?.notes" class="mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes:</h3>
      <div class="bg-gray-50 p-4 rounded border text-gray-700 whitespace-pre-wrap">
        {{ props.formValues.notes }}
      </div>
    </div>

    <!-- Footer -->
    <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-600">
      <p>
        Thank you for your business! Payment is due within
        {{ props.formValues?.due_days || '—' }} days.
      </p>
      <p class="mt-2">
        Please make checks payable to "Your Company Name" and include the invoice number on your
        payment.
      </p>
    </div>
  </div>
</template>