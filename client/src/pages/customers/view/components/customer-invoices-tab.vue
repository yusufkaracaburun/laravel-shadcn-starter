<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import { FileTextIcon } from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

// Use invoices directly from customer data
const customerInvoices = computed(() => {
  return props.customer.invoices ?? []
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
    <div
      v-if="!customerInvoices || customerInvoices.length === 0"
      class="flex flex-col items-center justify-center py-12 text-center"
    >
      <div class="rounded-full bg-muted p-3 mb-4">
        <FileTextIcon class="size-6 text-muted-foreground" />
      </div>
      <p class="text-sm font-medium text-muted-foreground mb-1">
        Geen facturen gevonden
      </p>
      <p class="text-xs text-muted-foreground">
        Er zijn nog geen facturen gekoppeld aan deze klant.
      </p>
    </div>
    <div v-else class="space-y-2">
      <div
        v-for="invoice in customerInvoices"
        :key="invoice.id"
        class="flex items-start gap-3 p-3 rounded-lg border hover:bg-muted/30 transition-colors cursor-pointer"
      >
        <FileTextIcon class="size-5 text-muted-foreground shrink-0 mt-0.5" />
        <div class="flex-1 min-w-0 space-y-2">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-medium text-sm">
              {{ invoice.invoice_number || `Factuur #${invoice.id}` }}
            </span>
            <Badge
              :variant="getStatusBadgeVariant(invoice.status)"
              class="shrink-0 text-xs"
            >
              {{ getStatusLabel(invoice.status) }}
            </Badge>
          </div>
          <div v-if="invoice.notes" class="text-xs text-muted-foreground line-clamp-2">
            {{ invoice.notes }}
          </div>
          <div class="flex items-center justify-between pt-2 border-t">
            <div class="text-sm font-semibold">
              {{ formatMoney(invoice.total) }}
            </div>
            <div class="text-xs text-muted-foreground">
              {{ formatDate(invoice.date) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
