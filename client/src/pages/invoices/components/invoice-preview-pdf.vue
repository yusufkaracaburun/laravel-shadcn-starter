<script setup lang="ts">
import { Receipt } from 'lucide-vue-next'

import type { TInvoice } from '@/pages/invoices/data/schema'
import type { IInvoiceItem } from '@/services/invoices.service'

import {
  formatDateForPreview,
  formatMoney,
  formatNumber,
} from '@/pages/invoices/utils/formatters'

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
  <div class="bg-white print:p-0 p-8 max-w-[800px] mx-auto">
    <!-- HEADER -->
    <div class="flex justify-between items-start mb-10">
      <!-- Logo -->
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center"
        >
          <Receipt class="w-5 h-5 text-white" />
        </div>
      </div>

      <!-- Invoice meta -->
      <div class="text-right text-sm">
        <div class="font-semibold">Factuur</div>
        <div>Factuur #{{ props.formValues?.invoice_number ?? '—' }}</div>
        <div>Datum {{ formatDate(props.formValues?.date ?? null) }}</div>
        <div>
          Vervaldatum {{ formatDate(props.formValues?.date_due ?? null) }}
        </div>
      </div>
    </div>

    <!-- CUSTOMER -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold mb-2">Klant</h2>
      <div class="text-sm leading-relaxed">
        <div class="font-medium">
          {{ props.formValues?.customer?.name ?? '—' }}
        </div>
        <div v-if="props.formValues?.customer?.address">
          {{ props.formValues.customer.address }}
        </div>
        <div
          v-if="
            props.formValues?.customer?.zipcode ||
            props.formValues?.customer?.city
          "
        >
          {{ props.formValues?.customer?.zipcode }}
          {{ props.formValues?.customer?.city }}
        </div>
      </div>
    </div>

    <!-- ITEMS -->
    <table class="w-full text-sm mb-10 border-collapse">
      <thead>
        <tr class="text-muted border-b">
          <th class="text-left py-2">Omschrijving</th>
          <th class="text-center py-2">Aantal</th>
          <th class="text-center py-2">BTW%</th>
          <th class="text-right py-2">Prijs (excl.)</th>
          <th class="text-right py-2">Totaal (excl.)</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          class="border-b last:border-b-0"
        >
          <td class="py-2">
            {{ item.description || '—' }}
          </td>
          <td class="text-center py-2">
            {{ formatNumber(item.quantity, 2) }}
          </td>
          <td class="text-center py-2">{{ item.vat_rate }}%</td>
          <td class="text-right py-2">
            {{ formatCurrency(item.unit_price) }}
          </td>
          <td class="text-right py-2">
            {{ formatCurrency(item.total_excl_vat) }}
          </td>
        </tr>
      </tbody>
    </table>

    <!-- TOTALS -->
    <div class="flex justify-end mb-10">
      <div class="w-72 bg-gray-50 p-4 text-sm">
        <div class="flex justify-between mb-2">
          <span>Subtotaal (excl. BTW)</span>
          <span>{{ formatCurrency(props.formValues?.subtotal) }}</span>
        </div>

        <div
          v-if="props.formValues?.total_vat_21"
          class="flex justify-between mb-2"
        >
          <span>BTW 21%</span>
          <span>{{ formatCurrency(props.formValues.total_vat_21) }}</span>
        </div>

        <div class="border-t pt-2 mt-2 font-semibold flex justify-between">
          <span>Totaal (incl. BTW)</span>
          <span>{{ formatCurrency(props.formValues?.total) }}</span>
        </div>
      </div>
    </div>

    <!-- NOTES -->
    <div v-if="props.formValues?.notes" class="mb-12 text-sm">
      <h3 class="font-semibold mb-2">Opmerkingen</h3>
      <p class="whitespace-pre-line text-gray-700">
        {{ props.formValues.notes }}
      </p>
    </div>

    <!-- FOOTER -->
    <div class="border-t pt-4 text-xs text-gray-500 flex justify-between">
      <div>Bedrijfsnaam · info@bedrijf.nl · BTW nummer</div>
      <div class="text-center">aaa<br />bbb<br />ccc</div>
      <div class="text-right">aaa<br />bbb<br />ccc</div>
    </div>
  </div>
</template>
