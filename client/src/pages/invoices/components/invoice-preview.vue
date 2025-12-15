<script setup lang="ts">
import { computed } from 'vue'

import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { StatusBadge } from '@/components/ui/status-badge'
import type { Customer } from '@/services/customers.service'

import { statuses } from '../data/data'
import { formatDateForPreview, formatMoney, formatNumber } from '../utils/formatters'
import type { InvoiceItem } from '../data/schema'
import { calculateInvoiceTotals } from '../utils/calculations'

interface Props {
  formValues: {
    customer_id?: number
    invoice_number?: string | null
    date?: string
    date_due?: string
    status?: string
    notes?: string | null
    subtotal?: number | { formatted: string }
    total_vat_0?: number | { formatted: string }
    total_vat_9?: number | { formatted: string }
    total_vat_21?: number | { formatted: string }
    total?: number | { formatted: string }
  }
  items?: InvoiceItem[]
  customers?: Customer[]
  isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
})

// Use customers from props (from prerequisites) or fallback to empty array
const customers = computed(() => props.customers ?? [])

const selectedCustomer = computed(() => {
  if (!props.formValues.customer_id) return null
  return customers.value.find((c) => c.id === props.formValues.customer_id) || null
})

const currentStatus = computed(() => {
  return statuses.find((s) => s.value === props.formValues.status) || statuses[0]
})

// Calculate totals from items if provided
const calculatedTotals = computed(() => {
  if (props.items && props.items.length > 0) {
    return calculateInvoiceTotals(props.items)
  }
  return null
})

// Use calculated totals if available, otherwise use formValues
const displayTotals = computed(() => {
  if (calculatedTotals.value) {
    return {
      subtotal: calculatedTotals.value.subtotal,
      total_vat_0: calculatedTotals.value.totalVat0,
      total_vat_9: calculatedTotals.value.totalVat9,
      total_vat_21: calculatedTotals.value.totalVat21,
      total: calculatedTotals.value.total,
    }
  }
  return {
    subtotal: props.formValues.subtotal ?? 0,
    total_vat_0: props.formValues.total_vat_0 ?? 0,
    total_vat_9: props.formValues.total_vat_9 ?? 0,
    total_vat_21: props.formValues.total_vat_21 ?? 0,
    total: props.formValues.total ?? 0,
  }
})
</script>

<template>
  <Card class="sticky top-4 h-fit">
    <CardHeader>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Invoice Preview</h3>
        <StatusBadge
          v-if="formValues.status"
          :status="formValues.status"
          type="invoice"
          :icon="currentStatus.icon"
          :label="currentStatus.label"
        />
      </div>
    </CardHeader>
    <CardContent class="space-y-6">
      <!-- Invoice Header -->
      <div class="space-y-2 border-b pb-4">
        <div v-if="formValues.invoice_number" class="text-2xl font-bold">
          {{ formValues.invoice_number }}
        </div>
        <div v-else class="text-2xl font-bold text-muted-foreground">Invoice #—</div>

        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-muted-foreground">Issue Date</p>
            <p class="font-medium">
              {{ formValues.date ? formatDateForPreview(formValues.date) : '—' }}
            </p>
          </div>
          <div>
            <p class="text-muted-foreground">Due Date</p>
            <p class="font-medium">
              {{ formValues.date_due ? formatDateForPreview(formValues.date_due) : '—' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Bill To Section -->
      <div class="space-y-2 border-b pb-4">
        <h4 class="font-semibold text-sm uppercase text-muted-foreground">Bill To</h4>
        <div v-if="selectedCustomer" class="space-y-1">
          <p class="font-medium">{{ selectedCustomer.name }}</p>
          <div
            v-if="selectedCustomer.formatted_address?.length"
            class="text-sm text-muted-foreground"
          >
            <p v-for="(line, index) in selectedCustomer.formatted_address" :key="index">
              {{ line }}
            </p>
          </div>
          <div v-else-if="selectedCustomer.address" class="text-sm text-muted-foreground">
            <p>{{ selectedCustomer.address }}</p>
          </div>
          <div v-if="selectedCustomer.primary_contact?.name" class="text-sm text-muted-foreground">
            <p>Contact: {{ selectedCustomer.primary_contact.name }}</p>
          </div>
          <div v-if="selectedCustomer.email" class="text-sm text-muted-foreground">
            <p>Email: {{ selectedCustomer.email }}</p>
          </div>
        </div>
        <div v-else class="text-sm text-muted-foreground italic">No customer selected</div>
      </div>

      <!-- Items Section -->
      <div v-if="items && items.length > 0" class="space-y-2 border-b pb-4">
        <h4 class="font-semibold text-sm uppercase text-muted-foreground">Items</h4>
        <div class="space-y-2">
          <div v-for="item in items" :key="item.id" class="flex justify-between text-sm">
            <div class="flex-1">
              <p class="font-medium">{{ item.description || '—' }}</p>
              <p class="text-xs text-muted-foreground">
                {{ formatNumber(item.quantity, 2)
                }}{{ (item as any).unit ? ` ${(item as any).unit}` : '' }} ×
                {{ formatMoney(item.unit_price) }} @ {{ item.vat_rate }}%
              </p>
            </div>
            <div class="text-right">
              <p class="font-medium">{{ formatMoney(item.total_incl_vat) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="space-y-3 border-b pb-4">
        <h4 class="font-semibold text-sm uppercase text-muted-foreground">Summary</h4>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Subtotal</span>
            <span class="font-medium">
              {{ formatMoney(displayTotals.subtotal) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 0%</span>
            <span class="font-medium">
              {{ formatMoney(displayTotals.total_vat_0) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 9%</span>
            <span class="font-medium">
              {{ formatMoney(displayTotals.total_vat_9) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 21%</span>
            <span class="font-medium">
              {{ formatMoney(displayTotals.total_vat_21) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Total -->
      <div class="flex justify-between border-t pt-4">
        <span class="text-lg font-semibold">Total</span>
        <span class="text-lg font-bold">
          {{ formatMoney(displayTotals.total) }}
        </span>
      </div>

      <!-- Notes -->
      <div v-if="formValues.notes" class="space-y-2 border-t pt-4">
        <h4 class="font-semibold text-sm uppercase text-muted-foreground">Notes</h4>
        <p class="text-sm whitespace-pre-wrap">{{ formValues.notes }}</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-4">
        <div class="text-sm text-muted-foreground">Updating preview...</div>
      </div>
    </CardContent>
  </Card>
</template>
