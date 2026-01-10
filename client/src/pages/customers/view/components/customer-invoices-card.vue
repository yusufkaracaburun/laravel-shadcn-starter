<script setup lang="ts">
import { computed, onMounted } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import Loading from '@/components/loading.vue'
import { FileTextIcon } from '@/composables/use-icons.composable'
import { useInvoices } from '@/composables/use-invoices.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

// Fetch invoices - backend should filter by customer_id
const {
  invoices,
  loading,
  filter,
  onFiltersChange,
  fetchInvoicesData,
} = useInvoices()

// Filter invoices for this customer
const customerInvoices = computed(() => {
  return invoices.value ?? []
})

// Initialize filter when component mounts
onMounted(() => {
  // Filter invoices by customer_id
  onFiltersChange({ ...filter.value, customer_id: props.customer.id })
  fetchInvoicesData()
})

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
  return '€ 0,00'
}

function formatDate(dateString: string | null | undefined): string {
  if (!dateString) {
    return '-'
  }
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }
  } catch {
    // Ignore parsing errors
  }
  return dateString
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <FileTextIcon class="size-5" />
        Invoices
      </CardTitle>
      <CardDescription>Invoices associated with this customer</CardDescription>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="flex items-center justify-center py-8">
        <Loading />
      </div>
      <div
        v-else-if="!customerInvoices || customerInvoices.length === 0"
        class="flex items-center justify-center py-8"
      >
        <p class="text-sm text-muted-foreground">
          No invoices found.
        </p>
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="invoice in customerInvoices"
          :key="invoice.id"
          class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors"
        >
          <div class="flex-1 min-w-0">
            <div class="font-medium truncate">
              {{ invoice.invoice_number || `Invoice #${invoice.id}` }}
            </div>
            <div class="text-sm text-muted-foreground">
              {{ formatDate(invoice.date) }}
            </div>
          </div>
          <div class="ml-4 text-right">
            <div class="font-semibold">
              {{ formatMoney(invoice.total) }}
            </div>
            <div class="text-xs text-muted-foreground">
              {{ invoice.status }}
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
