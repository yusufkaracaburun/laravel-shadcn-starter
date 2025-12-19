<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next'

import type { IInvoicePayment } from '@/services/invoices.service'

import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'

import { formatDate, formatMoney } from '../utils/formatters'

interface IProps {
  payments: IInvoicePayment[]
}

const props = defineProps<IProps>()
</script>

<template>
  <Card>
    <CardHeader class="pb-4">
      <div class="flex items-center gap-2">
        <CreditCard class="size-4 text-muted-foreground" />
        <CardTitle class="text-base font-semibold">
          Payments
        </CardTitle>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="props.payments.length > 0" class="space-y-4">
        <div
          v-for="payment in props.payments"
          :key="payment.id"
          class="space-y-2"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-foreground">
                {{ formatMoney(payment.amount) }}
              </span>
              <Badge
                :class="`text-xs ${payment.status_formatted.style || 'bg-gray-100 text-gray-800'}`"
              >
                {{ payment.status_formatted.label }}
              </Badge>
            </div>
            <span class="text-xs text-muted-foreground">
              {{ formatDate(payment.date) }}
            </span>
          </div>
          <div
            v-if="payment.method"
            class="flex items-center gap-2 text-xs text-muted-foreground"
          >
            <span>Method:</span>
            <span class="font-medium">{{ payment.method }}</span>
          </div>
          <Separator
            v-if="payment !== props.payments[props.payments.length - 1]"
          />
        </div>
      </div>
      <div v-else class="py-8 text-center">
        <div
          class="inline-flex items-center justify-center size-10 rounded-full bg-muted mb-3"
        >
          <CreditCard class="size-5 text-muted-foreground" />
        </div>
        <p class="text-sm font-medium text-muted-foreground">
          No payments recorded
        </p>
        <p class="text-xs text-muted-foreground mt-1">
          Payments will appear here when recorded.
        </p>
      </div>
    </CardContent>
  </Card>
</template>
