<script setup lang="ts">
import { computed } from 'vue'

import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { StatusBadge } from '@/components/ui/status-badge'
import { useGetCustomersQuery } from '@/services/customers.service'

import { statuses } from '../data/data'
import { formatDateForPreview, formatMoney } from '../utils/formatters'

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
  isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
})

// Fetch customers to get customer details for preview
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => customersResponse.value?.data?.data ?? [])

const selectedCustomer = computed(() => {
  if (!props.formValues.customer_id) return null
  return customers.value.find((c) => c.id === props.formValues.customer_id) || null
})

const currentStatus = computed(() => {
  return statuses.find((s) => s.value === props.formValues.status) || statuses[0]
})
</script>

<template>
  <Card class="sticky top-4 h-fit">
    <CardHeader>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Invoice Preview</h3>
        <StatusBadge v-if="formValues.status" :status="formValues.status" type="invoice" :icon="currentStatus.icon"
          :label="currentStatus.label" />
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
          <div v-if="selectedCustomer.formatted_address?.length" class="text-sm text-muted-foreground">
            <p v-for="(line, index) in selectedCustomer.formatted_address" :key="index">
              {{ line }}
            </p>
          </div>
          <div v-else-if="selectedCustomer.address" class="text-sm text-muted-foreground">
            <p>{{ selectedCustomer.address }}</p>
          </div>
        </div>
        <div v-else class="text-sm text-muted-foreground italic">No customer selected</div>
      </div>

      <!-- Financial Summary -->
      <div class="space-y-3 border-b pb-4">
        <h4 class="font-semibold text-sm uppercase text-muted-foreground">Summary</h4>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Subtotal</span>
            <span class="font-medium">
              {{ formValues.subtotal ? formatMoney(formValues.subtotal) : formatMoney(0) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 0%</span>
            <span class="font-medium">
              {{ formValues.total_vat_0 ? formatMoney(formValues.total_vat_0) : formatMoney(0) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 9%</span>
            <span class="font-medium">
              {{ formValues.total_vat_9 ? formatMoney(formValues.total_vat_9) : formatMoney(0) }}
            </span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">VAT 21%</span>
            <span class="font-medium">
              {{ formValues.total_vat_21 ? formatMoney(formValues.total_vat_21) : formatMoney(0) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Total -->
      <div class="flex justify-between border-t pt-4">
        <span class="text-lg font-semibold">Total</span>
        <span class="text-lg font-bold">
          {{ formValues.total ? formatMoney(formValues.total) : formatMoney(0) }}
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