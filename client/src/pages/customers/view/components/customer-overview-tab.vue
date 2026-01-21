<script setup lang="ts">
import { computed, onMounted } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import Loading from '@/components/loading.vue'
import { FileTextIcon } from '@/composables/use-icons.composable'
import { useInvoices } from '@/pages/invoices/composables/use-invoices.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

// Fetch invoices - backend should filter by customer_id
const { invoices, loading, filter, onFiltersChange, fetchInvoicesData } =
  useInvoices()

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
      return date.toLocaleDateString('nl-NL', {
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

function getStatusBadgeVariant(status: string): 'default' | 'destructive' | 'secondary' {
  if (status === 'expired' || status === 'overdue') {
    return 'destructive'
  }
  if (status === 'paid') {
    return 'default'
  }
  return 'secondary'
}

function getStatusLabel(status: string): string {
  const statusMap: Record<string, string> = {
    expired: 'Verlopen',
    overdue: 'Verlopen',
    paid: 'Betaald',
    pending: 'In behandeling',
    draft: 'Concept',
  }
  return statusMap[status] || status
}
</script>

<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-8">
      <Loading />
    </div>
    <div
      v-else-if="!customerInvoices || customerInvoices.length === 0"
      class="flex items-center justify-center py-8"
    >
      <p class="text-sm text-muted-foreground">Geen facturen gevonden.</p>
    </div>
    <div v-else class="space-y-3">
      <div
        v-for="invoice in customerInvoices"
        :key="invoice.id"
        class="flex items-start gap-4 p-4 rounded-lg border hover:bg-muted/50 transition-colors"
      >
        <FileTextIcon class="size-5 text-muted-foreground shrink-0 mt-0.5" />
        <div class="flex-1 min-w-0 space-y-2">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-medium">
              {{ customer.name }} • {{ invoice.invoice_number || invoice.id }}
            </span>
            <Badge
              :variant="getStatusBadgeVariant(invoice.status)"
              class="shrink-0"
            >
              {{ getStatusLabel(invoice.status) }}
            </Badge>
          </div>
          <div class="text-sm text-muted-foreground">
            omschrijving
          </div>
          <div class="flex items-center justify-between">
            <div class="text-sm font-medium">
              {{ formatMoney(invoice.total) }}
            </div>
            <div class="text-sm text-muted-foreground">
              {{ formatDate(invoice.date) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
