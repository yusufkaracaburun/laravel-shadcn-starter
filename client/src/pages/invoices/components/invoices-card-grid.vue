<script setup lang="ts">
import type { IInvoice } from '@/services/invoices.service'

import Loading from '@/components/loading.vue'

import InvoiceCard from './invoice-card.vue'

interface IProps {
  invoices: IInvoice[]
  loading?: boolean
}

withDefaults(defineProps<IProps>(), {
  loading: false,
})
</script>

<template>
  <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
    <Loading />
  </div>
  <div v-else-if="invoices.length === 0" class="flex items-center justify-center min-h-[400px]">
    <div class="text-center">
      <p class="text-muted-foreground">No invoices found.</p>
    </div>
  </div>
  <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <InvoiceCard v-for="invoice in invoices" :key="invoice.id" :invoice="invoice" />
  </div>
</template>
