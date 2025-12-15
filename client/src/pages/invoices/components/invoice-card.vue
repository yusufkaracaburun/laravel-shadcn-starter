<script setup lang="ts">
import { MoreVertical, FileText } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { Invoice } from '@/services/invoices.service'

import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { StatusBadge } from '@/components/ui/status-badge'

import { statuses } from '../data/data'
import InvoiceDelete from './invoice-delete.vue'

interface Props {
  invoice: Invoice
}

const props = defineProps<Props>()
const router = useRouter()

const showComponent = shallowRef<typeof InvoiceDelete | null>(null)
const isDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({ name: '/invoices/[id]', params: { id: props.invoice.id.toString() } })
      break
    case 'edit':
      router.push({ name: '/invoices/edit-[id]', params: { id: props.invoice.id.toString() } })
      break
    case 'delete':
      showComponent.value = InvoiceDelete
      isDialogOpen.value = true
      break
  }
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
  return '€ 0,00'
}

function formatDate(dateString: string | null | undefined): string {
  if (!dateString) {
    return '-'
  }
  try {
    const date = new Date(dateString)
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    }
  } catch (error) {
    // Ignore parsing errors
  }
  return dateString
}
</script>

<template>
  <Card
    class="hover:shadow-md transition-shadow cursor-pointer"
    @click="router.push({ name: '/invoices/[id]', params: { id: invoice.id.toString() } })"
  >
    <CardHeader>
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-2">
          <FileText class="size-5 text-muted-foreground" />
          <CardTitle class="line-clamp-1">
            {{ invoice.invoice_number || `Invoice #${invoice.id}` }}
          </CardTitle>
        </div>
        <UiDropdownMenu @click.stop>
          <UiDropdownMenuTrigger as-child @click.stop>
            <UiButton variant="ghost" size="icon" class="size-8" @click.stop>
              <MoreVertical class="size-4" />
              <span class="sr-only">Open menu</span>
            </UiButton>
          </UiDropdownMenuTrigger>
          <UiDropdownMenuContent align="end" @click.stop>
            <UiDropdownMenuItem @click.stop="handleSelect('view')"> View </UiDropdownMenuItem>
            <UiDropdownMenuItem @click.stop="handleSelect('edit')"> Edit </UiDropdownMenuItem>
            <UiDropdownMenuItem class="text-destructive" @click.stop="handleSelect('delete')">
              Delete
            </UiDropdownMenuItem>
          </UiDropdownMenuContent>
        </UiDropdownMenu>
      </div>
      <div class="flex items-center gap-2 mt-2">
        <StatusBadge
          :status="invoice.status"
          type="invoice"
          :icon="statuses.find((s) => s.value === invoice.status)?.icon"
          :label="statuses.find((s) => s.value === invoice.status)?.label"
        />
      </div>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <div v-if="invoice.customer" class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Customer</span>
          <span class="text-sm font-medium truncate max-w-[150px]">{{
            invoice.customer.name || `Customer #${invoice.customer_id}`
          }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Date</span>
          <span class="text-sm font-medium">{{ formatDate(invoice.date) }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Due Date</span>
          <span class="text-sm font-medium">{{ formatDate(invoice.date_due) }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Total</span>
          <span class="text-sm font-semibold">{{ formatMoney(invoice.total) }}</span>
        </div>
      </div>
    </CardContent>
    <CardFooter class="text-xs text-muted-foreground">
      Created {{ formatDate(invoice.created_at) }}
    </CardFooter>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent" class="sm:max-w-[425px]">
        <component :is="showComponent" :invoice="invoice" @close="isDialogOpen = false" />
      </UiDialogContent>
    </UiDialog>
  </Card>
</template>
